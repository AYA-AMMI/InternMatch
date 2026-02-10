<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Student;
use App\Entity\Internship;
use App\Repository\ApplicationRepository;
use App\Repository\CompanyRepository;
use App\Repository\InternshipRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager)
    {}

    #[Route('/',name:'admin_dashboard')]
    public function dashboard(
        StudentRepository $studentRepo,
        CompanyRepository $companyRepo,
        InternshipRepository $internshipRepo,
        ApplicationRepository $applicationRepo
    ):Response{
        //Get global statistics:
        $totalStudents = $studentRepo->count([]);
        $totalCompanies = $companyRepo->count([]);
        $totalInternships = $internshipRepo->count([]);
        $totalApplications = $applicationRepo->count([]);

        // Get all applicationsfor charts :
        $allApplications = $applicationRepo->findAll();

        //calculate distribution of application-status for the Pie chart:
        $applicationsByStatus = [
            'pending' => 0,
            'accepted' => 0,
            'rejected' => 0,
        ];

        foreach($allApplications as $application){
            $status = $application->getStatus();
            if(isset($applicationsByStatus[$status])){
                $applicationsByStatus[$status]++;
            }
        }
        //calculate internships by industry for the bar Chart:
        $allInternships = $internshipRepo->findAll();
        $internshipsByIndustry = [];
        foreach($allInternships as $internship){
            $industry = $internship->getCompany()->getIndustry();
            if(!isset($internshipsByIndustry[$industry])){
                $internshipsByIndustry[$industry] = 0;
            }
            $internshipsByIndustry[$industry]++;
        }

        //Calculate registration evolution for Line chart (the last 6 months):
        $registrationStats = $this->calculateRegistrationEvolution($studentRepo, $companyRepo);

        //Get recent activity (last 10 applications):
        $recentApplications = $applicationRepo->findBy([],['appliedAt' => 'DESC'],10);

        return $this->render('admin/dashboard.html.twig',[
            'totalStudents' => $totalStudents,
            'totalCompanies' => $totalCompanies,
            'totalInternships' =>$totalInternships,
            'totalApplications' => $totalApplications,
            'applicationsByStatus' => $applicationsByStatus,
            'internshipsByIndustry' => $internshipsByIndustry,
            'registrationStats' => $registrationStats,
            'recentApplications' => $recentApplications,
        ]);
    }

    #[Route('/users',name:'admin_users')]
    public function users(Request $request, StudentRepository $studentRepo,CompanyRepository $companyRepo): Response{
        //Get search parameters:
        $studentSearch = $request->query->get('student_search', '');
        $companySearch = $request->query->get('company-search', '');
        
        // Get students with optional search:
        if($studentSearch){
            $students = $studentRepo->createQueryBuilder('s')
            ->where('s.firstName LIKE :search OR s.lastName LIKE :search OR s.email LIKE :search')
            ->setParameter('search','%'.$studentSearch.'%')
            ->orderBy('s.createdAt','DESC')
            ->getQuery()
            ->getResult();
        }else{
            $students = $studentRepo->findBy([],['createdAt'=> 'DESC']);
        }

        //Get companies with optional serach:
        if($companySearch){
            $companies = $companyRepo->createQueryBuilder('c')
            ->where('c.companyName LIKE :search OR c.email LIKE :search OR c.industry LIKE :search')
            ->setParameter('search','%'.$companySearch.'%')
            ->orderBy('c.createdAt','DESC')
            ->getQuery()
            ->getResult();
        }else{
            $companies = $companyRepo->findBy([],['createdAt'=> 'DESC']);
        }

        return $this->render('admin/users.html.twig',[
            'students' => $students,
            'companies' => $companies,
            'studentSearch' => $studentSearch,
            'companySearch' => $companySearch,
        ]);
    }
    #[Route('/user/student/{id}', name:'admin_student_details',methods:['GET'])]
    public function studentDetails(Student $student): JsonResponse{
        return $this->json([
            'id' => $student->getId(),
            'firtName' => $student->getFirstName(),
            'lastName' => $student->getLastName(),
            'email' => $student->getEmail(),
            'phone' => $student->getPhone(),
            'skills' => $student->getSkills(),
            'bio' => $student->getBio(),
            'expectedLocation' => $student->getExpectedLocation(),
            'expectedDuration' => $student->getExpectedDuration(),
            'createdAt' => $student->getCreatedAt()->format('Y-m-d H:i:s'),
            'totalApplications' => $student->getApplications()->count(),
        ]);
    }
    #[Route('/user/company/{id}', name:'admin_company_details',methods:['GET'])]
    public function companyDetails(Company $company): JsonResponse{
        return $this->json([
            'id' => $company->getId(),
            'companyName' => $company->getCompanyName(),
            'email' => $company->getEmail(),
            'industry' => $company->getIndustry(),
            'location' => $company->getLocation(),
            'description' => $company->getDescription(),
            'website' => $company->getWebsite(),
            'isVerified' => $company->isVerified(),
            'createdAt' => $company->getCreatedAt()->format('Y-m-d H:i:s'),
            'totalInternships' => $company->getInternships()->count(),
        ]);
    }

    #[Route('/user/student/{id}/delete',name:'admin_student_delete',methods:['POST'])]
    public function deleteStudent(Student $student, EntityManagerInterface $entityManager):Response{
        try{
            $applications = $student->getApplications();
            foreach($applications as $application){
                $entityManager->remove($application);
            }

            $entityManager->remove($student);
            $entityManager->flush();

            $this->addFlash('success', 'Student account deleted successfully');
        }catch(\Exception $e){
            $this->addFlash('error','Failed to delete student account.');
        }
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/user/company/{id}/delete',name:'admin_company_delete',methods:['POST'])]
    public function deleteCompany(Company $company, EntityManagerInterface $entityManager):Response{
        try{
            $internships = $company->getInternships();
            foreach($internships as $internship){
                $applications = $internship->getApplications();
                foreach($applications as $application){
                    $entityManager->remove($application);
                }
                $entityManager->remove($internship);
            }
            $entityManager->remove($company);
            $entityManager->flush();

            $this->addFlash('success', 'Company account deleted successfully');
        }catch(\Exception $e){
            $this->addFlash('error','Failed to delete company account.');
        }
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/user/company/{id}/toggle-verification',name:'admin_company_toggle_verification',methods:['POST'])]
    public function toggleCompanyVerification(Company $company, EntityManagerInterface $entityManager):Response{
        $newStatus = !$company->isVerified();
        $company->setIsVerified($newStatus);
        $entityManager->flush();

        $statusText = $newStatus? 'verified' : 'unverified';
        $this->addFlash('success', "Company {$company->getCompanyName()} is now {$statusText}.");

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/internships', name:'admin_internships')]
    public function internships(Request $request, InternshipRepository $internshipRepo):Response{
        //get filter parameters:
        $status = $request->query->get('status','');
        $industry= $request->query->get('industry','');
        $search = $request->query->get('search','');

        //Build query
        $qb = $internshipRepo->createQueryBuilder('i')
            ->leftJoin('i.company','c')
            ->addSelect('c');
        if($status){
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }
        if($industry){
            $qb->andWhere('c.industry = :industry')
                ->setParameter('industry', $industry);
        }
        if($search){
            $qb->andWhere('i.title LIKE :search OR c.companyName LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
        $internships = $qb->orderBy('i.postedAt','DESC')
            ->getQuery()
            ->getResult();
        
        //Get unique industries for filter dropdown:
        $allInternships = $internshipRepo->findAll();
        $industries = array_unique(array_map(fn($i) => $i->getCompany() -> getIndustry(),$allInternships));
        sort($industries);

        return $this->render('admin/internships.html.twig',[
            'internships' => $internships,
            'industries' => $industries,
            'currentStatus' => $status,
            'currentIndustry' => $industry,
            'currentSearch' => $search,
        ]);
    }
    #[Route('/internship/{id}/toggle-status',name:'admin_internship_toggle_status',methods:['POST'])]
    public function toggleInternshipStatus(Internship $internship, EntityManagerInterface $entityManager): Response
    {
        $newStatus = $internship->getStatus() === 'active' ? 'closed' : 'active';
        $internship->setStatus($newStatus);
        $entityManager->flush();

        $this->addFlash('success', "Internship status changed to {$newStatus}.");
        return $this->redirectToRoute('admin_internships');
    }

    #[Route('/internship/{id}/delete', name: 'admin_internship_delete', methods: ['POST'])]
    public function deleteInternship(Internship $internship, EntityManagerInterface $entityManager): Response
    {
        try {
            $applications = $internship->getApplications();
            foreach($applications as $application){
                $entityManager->remove($application);
            }
            $entityManager->remove($internship);
            $entityManager->flush();

            $this->addFlash('success', 'Internship deleted successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete internship.');
        }

        return $this->redirectToRoute('admin_internships');
    }

    #[Route('/statistics', name: 'admin_statistics')]
    public function statistics(
        StudentRepository $studentRepo,
        CompanyRepository $companyRepo,
        InternshipRepository $internshipRepo,
        ApplicationRepository $applicationRepo
    ): Response {

        // Calculate application success rate
        $totalApplications = $applicationRepo->count([]);
        $acceptedApplications = $applicationRepo->count(['status' => 'accepted']);
        $successRate = $totalApplications > 0 ? ($acceptedApplications / $totalApplications) * 100 : 0;

        // Get top industries by internship count
        $allInternships = $internshipRepo->findAll();
        $industryStats = [];
        foreach ($allInternships as $internship) {
            $industry = $internship->getCompany()->getIndustry();
            if (!isset($industryStats[$industry])) {
                $industryStats[$industry] = [
                    'internships' => 0,
                    'applications' => 0,
                ];
            }
            $industryStats[$industry]['internships']++;
            $industryStats[$industry]['applications'] += $internship->getApplications()->count();
        }

        // Sort industries by internship count
        uasort($industryStats, fn($a, $b) => $b['internships'] - $a['internships']);
        $topIndustries = array_slice($industryStats, 0, 5, true);

        // Calculate average match score
        $allApplications = $applicationRepo->findAll();
        $totalMatchScore = 0;
        foreach ($allApplications as $app) {
            $totalMatchScore += $app->getMatchScore();
        }
        $avgMatchScore = $totalApplications > 0 ? $totalMatchScore / $totalApplications : 0;

        // Get most active companies (by application count)
        $companies = $companyRepo->findAll();
        $companyStats = [];
        foreach ($companies as $company) {
            $applicationCount = 0;
            foreach ($company->getInternships() as $internship) {
                $applicationCount += $internship->getApplications()->count();
            }
            if ($applicationCount > 0) {
                $companyStats[] = [
                    'company' => $company,
                    'applicationCount' => $applicationCount,
                    'internshipCount' => $company->getInternships()->count(),
                ];
            }
        }
        usort($companyStats, fn($a, $b) => $b['applicationCount'] - $a['applicationCount']);
        $mostActiveCompanies = array_slice($companyStats, 0, 5);

        // Monthly trends (last 6 months)
        $monthlyTrends = $this->calculateMonthlyTrends($applicationRepo, $internshipRepo);

        // Match score distribution (for histogram)
        $matchScoreDistribution = $this->calculateMatchScoreDistribution($allApplications);

        return $this->render('admin/statistics.html.twig', [
            'successRate' => round($successRate, 2),
            'topIndustries' => $topIndustries,
            'avgMatchScore' => round($avgMatchScore, 2),
            'mostActiveCompanies' => $mostActiveCompanies,
            'monthlyTrends' => $monthlyTrends,
            'matchScoreDistribution' => $matchScoreDistribution,
            'totalApplications' => $totalApplications,
            'acceptedApplications' => $acceptedApplications,
        ]);
    }
    #[Route('/statistics/export', name: 'admin_statistics_export')]
    public function exportStatistics(
        StudentRepository $studentRepo,
        CompanyRepository $companyRepo,
        ApplicationRepository $applicationRepo
    ): Response {
        // Create CSV content
        $csv = "Type,Count,Date\n";
        $csv .= "Total Students," . $studentRepo->count([]) . "," . date('Y-m-d') . "\n";
        $csv .= "Total Companies," . $companyRepo->count([]) . "," . date('Y-m-d') . "\n";
        $csv .= "Total Applications," . $applicationRepo->count([]) . "," . date('Y-m-d') . "\n";
        $csv .= "Pending Applications," . $applicationRepo->count(['status' => 'pending']) . "," . date('Y-m-d') . "\n";
        $csv .= "Accepted Applications," . $applicationRepo->count(['status' => 'accepted']) . "," . date('Y-m-d') . "\n";
        $csv .= "Rejected Applications," . $applicationRepo->count(['status' => 'rejected']) . "," . date('Y-m-d') . "\n";

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="internmatch_statistics_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Calculate registration evolution for students and companies
     * Returns data for Line Chart (last 6 months)
     */
    private function calculateRegistrationEvolution(
        StudentRepository $studentRepo,
        CompanyRepository $companyRepo
    ): array {
        $stats = [];
        $now = new \DateTime();

        // Last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = (clone $now)->modify("-{$i} months");
            $monthKey = $month->format('M Y');
            $stats[$monthKey] = [
                'students' => 0,
                'companies' => 0,
            ];
        }

        // Count students by registration month
        $students = $studentRepo->findAll();
        foreach ($students as $student) {
            $registrationMonth = $student->getCreatedAt()->format('M Y');
            if (isset($stats[$registrationMonth])) {
                $stats[$registrationMonth]['students']++;
            }
        }

        // Count companies by registration month
        $companies = $companyRepo->findAll();
        foreach ($companies as $company) {
            $registrationMonth = $company->getCreatedAt()->format('M Y');
            if (isset($stats[$registrationMonth])) {
                $stats[$registrationMonth]['companies']++;
            }
        }

        return $stats;
    }

    /**
     * Calculate monthly trends for applications and internships
     * Used in statistics page
     */
    private function calculateMonthlyTrends(
        ApplicationRepository $applicationRepo,
        InternshipRepository $internshipRepo
    ): array {
        $trends = [];
        $now = new \DateTime();

        // Last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = (clone $now)->modify("-{$i} months");
            $monthKey = $month->format('M Y');
            $trends[$monthKey] = [
                'applications' => 0,
                'internships' => 0,
            ];
        }

        // Count applications by month
        $applications = $applicationRepo->findAll();
        foreach ($applications as $application) {
            $appliedMonth = $application->getAppliedAt()->format('M Y');
            if (isset($trends[$appliedMonth])) {
                $trends[$appliedMonth]['applications']++;
            }
        }

        // Count internships by month
        $internships = $internshipRepo->findAll();
        foreach ($internships as $internship) {
            $postedMonth = $internship->getPostedAt()->format('M Y');
            if (isset($trends[$postedMonth])) {
                $trends[$postedMonth]['internships']++;
            }
        }

        return $trends;
    }

    /**
     * Calculate match score distribution for histogram
     * Groups scores into ranges: 0-20, 21-40, 41-60, 61-80, 81-100
     */
    private function calculateMatchScoreDistribution(array $applications): array
    {
        $distribution = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        foreach ($applications as $application) {
            $score = $application->getMatchScore();
            
            if ($score <= 20) {
                $distribution['0-20']++;
            } elseif ($score <= 40) {
                $distribution['21-40']++;
            } elseif ($score <= 60) {
                $distribution['41-60']++;
            } elseif ($score <= 80) {
                $distribution['61-80']++;
            } else {
                $distribution['81-100']++;
            }
        }

        return $distribution;
    }
}
