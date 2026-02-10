<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Internship;
use App\Entity\Student;
use App\Repository\InternshipRepository;
use App\Repository\ApplicationRepository;
use App\Service\MatchingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/student')]
#[IsGranted('ROLE_STUDENT')]
final class StudentController extends AbstractController
{
    public function __construct(
        private MatchingService $matchingService
    ) {}

    #[Route('/dashboard', name: 'student_dashboard')]
    public function dashboard(ApplicationRepository $applicationRepo, InternshipRepository $internshipRepo): Response
    {

        /** @var Student $student */
        $student = $this->getUser();

        //Get all Student applications:
        $applications = $applicationRepo->findBy(['student' => $student]);

        //Count applications by status:
        $totalApplications = count($applications);
        $pendingApplications = count(array_filter($applications, fn($app) => $app->getStatus() === 'pending'));
        $acceptedApplications = count(array_filter($applications, fn($app) => $app->getStatus() === 'accepted'));
        $rejectedApplications = count(array_filter($applications, fn($app) => $app->getStatus() === 'rejected'));

        //get all active internships:
        $allInternships = $internshipRepo->findBy(['status' => 'active']);

        //Calculate match scores and get top 5 matches:
        $internshipsWithScores = [];
        foreach($allInternships as $internship){
            //check if the student is already applied:
            $hasApplied = $applicationRepo->findBy([
                'student'=> $student,
                'internship' => $internship
            ]) !== null;
            if($hasApplied){
                $matchScore = $this->matchingService->calculateMatchScore($student,$internship);
                $internshipsWithScores[] = [
                    'internship' => $internship,
                    'score' => $matchScore
                ];
            }
        }
        // Sort internships by score descending and get top 5:
        usort($internshipsWithScores, fn($a,$b) => $b['score']  - $a['score']);
        $topMatches = array_slice($internshipsWithScores,0,5);

        return $this->render('student/dashboard.html.twig', [
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'rejectedApplications' => $rejectedApplications,
            'topMatches' => $topMatches,
            'allInternships' => $allInternships,
        ]);
    }

    #[Route(path:'/internships',name: 'student_internships')]
    public function browseInternships(InternshipRepository $internshipRepo, ApplicationRepository $applicationRepo):Response{
        /**@var Student $student */
        $student = $this->getUser();

        //get all active internships:
        $internships = $internshipRepo->findBy(['status' => 'active'],['postedAt' => 'DESC']);

        //Calculate match scores for each internship:
        $internshipsWithScores = [];
        foreach($internships as $internship){
            $hasApplied = $applicationRepo->findOneBy([
                'student'=> $student,
                'internship' => $internship
            ]) !== null;

            $matchScore = $this->matchingService->calculateMatchScore($student,$internship);

            $internshipsWithScores[] = [
                'internship' => $internship,
                'score' => $matchScore,
                'hasApplied' => $hasApplied
            ];   
        }
        //Get unique industries and loations for filters:
        $industries = array_unique(array_map(fn($i) => $i->getCompany()->getIndustry(), $internships));
        $locations = array_unique(array_map(fn($i) => $i->getLocation(), $internships));

        return $this->render('student/internships.html.twig', [
            'internships' => $internshipsWithScores,
            'industries' => $industries,
            'locations' => $locations,
                ]);
    }

    #[Route(path:'/internship/{id}',name:'student_internship_details')]
    public function internshipDetails(Internship $internship, ApplicationRepository $applicationRepo):Response{
        /**@var Student $student */
        $student = $this->getUser();

        //check if already applied :
        $application = $applicationRepo->findOneBy([
            'student' => $student,
            'internship' => $internship
        ]);
        $matchScore = $this->matchingService->calculateMatchScore($student,$internship);

        return $this->render('student/internship_details.html.twig', [
            'internship' => $internship,
            'matchScore' => $matchScore,
            'hasApplied' => $application !== null,
            'application' => $application,
                ]);
    }

    #[Route(path:'/apply/{id}',name:'student_apply',methods:['POST'])]
    public function applyToInternship(Internship $internship,
                    Request $request,
                    EntityManagerInterface $entityManager,
                    ApplicationRepository $applicationRepo) : Response{
        
                        /**@var Student $student */
        $student = $this->getUser();
        // Check if already applied
        $existingApplication = $applicationRepo->findOneBy([
            'student' => $student,
            'internship' => $internship
        ]);

        if ($existingApplication) {
            $this->addFlash('error', 'You have already applied to this internship.');
            return $this->redirectToRoute('student_internship_details', ['id' => $internship->getId()]);
        }
        // Check if internship is still active
        if ($internship->getStatus() !== 'active') {
            $this->addFlash('error', 'This internship is no longer available.');
            return $this->redirectToRoute('student_internships');
        }

        //create new application
        $application = new Application();
        $application->setStudent($student);
        $application->setInternship($internship);
        $application->setMessage($request->request->get('message'));

        //Calculate and set match score
        $matchScore = $this->matchingService->calculateMatchScore($student, $internship);
        $application->setMatchScore($matchScore);

        $entityManager->persist($application);
        $entityManager->flush();

        $this->addFlash('success', 'Application submitted successfully! The company will review your profile.');
        return $this->redirectToRoute('student_applications');
    }
    #[Route(path:'/applications',name:'student_applications')]
    public function myApplications(ApplicationRepository $applicationRepo) : Response{

        /** @var Student $student */
        $student = $this->getUser();

        $applications = $applicationRepo->findBy(
            ['student' => $student],
            ['appliedAt' => 'DESC']
        );

        return $this->render('student/applications.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route(path:'/application/{id}/withdraw',name:'student_withdraw_application',methods:['POST'])]
    public function withdrawAppliction(Application $application,EntityManagerInterface $entityManager): Response{
        /** @var Student $student */
        $student = $this->getUser();

        //verify ownerShip:
        if($application->getStudent()->getId() !== $student->getId()){
            throw $this->createAccessDeniedException();
        }

        //Only allow withdrawal if pending:
        if($application->getStatus() !== 'pending'){
            $this->addFlash('error', 'You can only withdraw pending applications.');
            return $this->redirectToRoute('student_applications');
        }
        $entityManager->remove($application);
        $entityManager->flush();

        $this->addFlash('success', 'Application withdrawn successfully.');
        return $this->redirectToRoute('student_applications');
    }
    #[Route(path:'/profile',name:'student_profile')]
    public function profile() : Response{
        /** @var Student $student */
        $student = $this->getUser();

        return $this->render('student/profile.html.twig',[
            'student' => $student,
        ]);
    }

    #[Route(path:'/profile/update',name:'student_profile_update',methods:['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager):Response{

        /** @var Student $student */
        $student = $this->getUser();

        //update student informations
        $student->setFirstName($request->request->get('firstName'));
        $student->setLastName($request->request->get('lastName'));
        $student->setPhone($request->request->get('phone'));
        $student->setBio($request->request->get('bio'));
        $student->setExpectedLocation($request->request->get('expectedLocation'));
        $student->setExpectedDuration((int)$request->request->get('expectedDuration'));

        // Handle skills array
        $skills = $request->request->all('skills');
        if (!empty($skills)) {
            $student->setSkills(array_filter($skills));
        }

        $entityManager->flush();

        $this->addFlash('success', 'Profile updated successfully!');
        return $this->redirectToRoute('student_profile');

    }
    // /** 
    // * Calculate match score between a student ans an internship (0-100) 
    // */
    // private function calculateMatchScore(Student $student,Internship $internship): int{
    //     $score = 0;
    //     // skills matching 50% of the totalScore:
    //     $studentSkills = $student->getSkills() ?? [];
    //     $requiredSkills = $internship->getRequiredSkills()?? [];
    //     if(!empty($requiredSkills)){
    //         $commonSkills = array_intersect(
    //             array_map('strtolower',$studentSkills),
    //             array_map('strtolower',$requiredSkills)
    //         );
    //         $skillsScore = (count($commonSkills)/count($requiredSkills)) * 50;
    //         $score += $skillsScore;
    //     }
    //     //location matching 25% of the totalScore
    //     $studentLocation = strtolower(trim($student->getExpectedLocation()?? ''));
    //     $internshipLocation = strtolower(trim($internship->getLocation() ?? ''));
    //     if($studentLocation === $internshipLocation){
    //         $score += 25;
    //     }elseif(!empty($studentLocation) && !empty($internshipLocation)){
    //         // Check if same city (partial match)
    //         if (str_contains($internshipLocation, $studentLocation) || str_contains($studentLocation, $internshipLocation)) {
    //             $score += 15;
    //         }
    //     }
    //     //Duration matching 15% of the totalScore
    //     $studentDuration = $student->getExpectedDuration();
    //     $internshipDuration = $internship->getDuration();

    //     if ($studentDuration === $internshipDuration) {
    //         $score += 15;
    //     } elseif (abs($studentDuration - $internshipDuration) <= 1) {
    //         $score += 10;
    //     } elseif (abs($studentDuration - $internshipDuration) <= 2) {
    //         $score += 5;
    //     }
    //     //industry preference 10% of the totalScore
    //     $score += 10;

    //     return min((int)$score, 100);
    // }
}
