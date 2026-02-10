<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Internship;
use App\Entity\Company;
use App\Repository\InternshipRepository;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/company')]
#[IsGranted('ROLE_COMPANY')]
final class CompanyController extends AbstractController
{
    #[Route('/dashboard', name: 'company_dashboard')]
    public function dashboard(InternshipRepository $internshipRepo,ApplicationRepository $applicationRepo ): Response
    {

        /** @var Company $company */
        $company = $this->getUser();

        //Get Company internships:
        $internships = $internshipRepo->findBy(['company' => $company]);
        $activeInternships = array_filter($internships, fn($i) => $i->getStatus() === 'active');

        // Get all applications for company internships:
        $allApplications = [];
        foreach($internships as $internship){
            foreach($internship->getApplications() as $application){
                $allApplications[] = $application; 
            }
        }

        //Count applications by status:
        $totalApplications = count($allApplications);
        $pendingApplications = count(array_filter($allApplications, fn($app) => $app->getStatus() === 'pending'));
        $acceptedApplications = count(array_filter($allApplications, fn($app) => $app->getStatus() === 'accepted'));

        //get recent applications (last 10):
        usort($allApplications,fn($a,$b) => $b->getAppliedAt() <=> $a->getAppliedAt() );
        $recentApplications = array_slice($allApplications,0,10);

        //calculate monthly application stats for chart:
        $monthlyStats = $this->calculateMonthlyStats($allApplications);

        return $this->render('company/dashboard.html.twig', [
            'activeInternships' => count($activeInternships),
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'recentApplications' => $recentApplications,
            'monthlyStats' => $monthlyStats,
        ]);
    }

    #[Route(path:'/internships',name: 'company_internships')]
    public function listInternships(InternshipRepository $internshipRepo):Response{

        /** @var Company $company */
        $company = $this->getUser();

        $internships = $internshipRepo->findBy(
            ['company' => $company],
            ['postedAt' => 'DESC']
        );

        return $this->render('company/internships.html.twig', [
            'internships' => $internships,
        ]);
    }

    #[Route(path:'/internship/create',name:'company_internship_create')]
    public function createInternship(Request $request,EntityManagerInterface $entityManager):Response{
        
        if($request->isMethod('POST')){
            /** @var Company $company */
            $company = $this->getUser();

            $internship = new Internship();
            $internship->setCompany($company);
            $internship->setTitle($request->request->get('title'));
            $internship->setDescription($request->request->get('description'));

            // Handle required skills (JSON array)
            $skillsJson = $request->request->get('requiredSkills');
            $skills = $skillsJson ? json_decode($skillsJson, true) : [];
            $internship->setRequiredSkills($skills);

            $internship->setLocation($request->request->get('location'));
            $internship->setDuration((int)$request->request->get('duration'));
            $internship->setSalary($request->request->get('salary'));

            //HAndle deadline:
            $deadlineStr = $request->request->get('deadline');
            if($deadlineStr){
                $deadline = \DateTimeImmutable::createFromFormat('Y-m-d',$deadlineStr);
                $internship->setDeadline($deadline);
            }
            $internship->setStatus('active');

            $entityManager->persist($internship);
            $entityManager->flush();

            $this->addFlash('success', 'Internship created successfully!');
            return $this->redirectToRoute('company_internships');

        }

        return $this->render('company/internship_form.html.twig', [
            'internship' => null,
        ]);
    }

    #[Route(path:'/internship/{id}/edit',name:'company_internship_edit')]
    public function editInternship(Internship $internship,Request $request,EntityManagerInterface $entityManager):Response{
        
        /** @var Company $company */
        $company = $this->getUser();

         // Verify ownership
        if ($internship->getCompany()->getId() !== $company->getId()) {
            throw $this->createAccessDeniedException();
        }

        if($request->isMethod('POST')){
            
            $internship->setTitle($request->request->get('title'));
            $internship->setDescription($request->request->get('description'));

            // Handle required skills
            $skillsJson = $request->request->get('requiredSkills');
            $skills = $skillsJson ? json_decode($skillsJson, true) : [];
            $internship->setRequiredSkills($skills);

            $internship->setLocation($request->request->get('location'));
            $internship->setDuration((int)$request->request->get('duration'));
            $internship->setSalary($request->request->get('salary'));

            //HAndle deadline:
            $deadlineStr = $request->request->get('deadline');
            if($deadlineStr){
                $deadline = \DateTimeImmutable::createFromFormat('Y-m-d',$deadlineStr);
                $internship->setDeadline($deadline);
            }
            $internship->setStatus($request->request->get('status'));

            $entityManager->flush();

            $this->addFlash('success', 'Internship updated successfully!');
            return $this->redirectToRoute('company_internships');

        }

        return $this->render('company/internship_form.html.twig', [
            'internship' => $internship,
        ]);
    } 
    
    #[Route(path:'/internship/{id}/delete',name:'company_internship_delete',methods:['POST'])]
    public function deleteInternship(Internship $internship,EntityManagerInterface $entityManager):Response{
        
        /** @var Company $company */
        $company = $this->getUser();

         // Verify ownership
        if ($internship->getCompany()->getId() !== $company->getId()) {
            throw $this->createAccessDeniedException();
        }

        try {
        //Supprimer toutes les applications
        $applications = $internship->getApplications();
        foreach ($applications as $application) {
            $entityManager->remove($application);
        }
        
        //Supprimer l'internship
        $entityManager->remove($internship);
        
        //Enregistrer
        $entityManager->flush();

        $this->addFlash('success', 'Internship deleted successfully!');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Failed to delete internship: ' . $e->getMessage());
    }
        return $this->redirectToRoute('company_internships');
    }

    #[Route(path:'/internship/{id}/toggle-status',name:'company_internship_toggle_status',methods:['POST'])]
    public function toggleInternshipStatus(Internship $internship,EntityManagerInterface $entityManager) : Response{
        
        /** @var Company $company */
        $company = $this->getUser();
        
        //verify ownerships
        if($internship->getCompany()->getId()!== $company->getId()){
            throw $this->createAccessDeniedException();
        }

        $newStatus = $internship->getStatus() === 'active' ? 'closed' : 'active';
        $internship->setStatus($newStatus);

        $entityManager->flush();
        
        $this->addFlash('success', "Internship status changed to {$newStatus}!");
        return $this->redirectToRoute('company_internships');
    }

    #[Route(path:'/applications',name:'company_applications')]
    public function viewApplications(ApplicationRepository $applicationRepo) : Response{

        /** @var Company $company */
        $company = $this->getUser();

        //Get all applicatins for this company's internships:
        $internships = $company->getInternships();
        $applications = [];

        foreach($internships as $internship){
            foreach($internship->getApplications() as $application){
                $applications[] = $application;
            } 
        }
        // sort by date (most recent first)
        usort($applications, fn($a,$b) => $b->getAppliedAt() <=> $a->getAppliedAt());


        return $this->render('company/applications.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route(path:'/application/{id}/accept',name:'company_application_accept',methods:['POST'])]
    public function acceptApplication(Application $application,EntityManagerInterface $entityManager): Response{
        /** @var Company $company */
        $company = $this->getUser();

        //verify ownerShip:
        if($application->getInternship()->getCompany()->getId() !== $company->getId()){
            throw $this->createAccessDeniedException();
        }

        $application->setStatus('accepted');
        $entityManager->flush();

        $this->addFlash('success', 'Application accepted successfully.');
        return $this->redirectToRoute('company_applications');
    }
    #[Route(path:'/application/{id}/reject',name:'company_application_reject',methods:['POST'])]
    public function rejectApplication(Application $application,EntityManagerInterface $entityManager): Response{
        /** @var Company $company */
        $company = $this->getUser();

        //verify ownerShip:
        if($application->getInternship()->getCompany()->getId() !== $company->getId()){
            throw $this->createAccessDeniedException();
        }

        $application->setStatus('rejected');
        $entityManager->flush();

        $this->addFlash('success', 'Application rejected.');
        return $this->redirectToRoute('company_applications');
    }
    #[Route(path:'/profile',name:'company_profile')]
    public function profile() : Response{
        /** @var Company $company */
        $company = $this->getUser();

        return $this->render('company/profile.html.twig',[
            'company' => $company,
        ]);
    }

    #[Route(path:'/profile/update',name:'company_profile_update',methods:['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager):Response{

        /** @var Company $company */
        $company = $this->getUser();

        //update company informations

        $company->setCompanyName($request->request->get('companyName'));
        $company->setIndustry($request->request->get('industry'));
        $company->setLocation($request->request->get('location'));
        $company->setDescription($request->request->get('description'));
        $company->setWebsite($request->request->get('website'));

        $entityManager->flush();

        $this->addFlash('success', 'Profile updated successfully!');
        return $this->redirectToRoute('company_profile');

    }
    /** 
    * Calculate monthly application statistics
    */
    private function calculateMonthlyStats(array $applications): array{
        $stats = [];
        $now = new \DateTime();

        //last 6 months
        for($i = 5; $i >= 0; $i--){
            $month = (clone $now)->modify("-{$i} months");
            $monthkey = $month->format('M Y');
            $stats[$monthkey] = 0;
        }
        //count applications per month
        foreach($applications as $application){
            $appliedMonth = $application->getAppliedAt()->format('M Y');
            if(isset($stats[$appliedMonth])){
                $stats[$appliedMonth]++;
            }
        }
        return $stats;
    }
}
