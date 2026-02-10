<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        // Si déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Traitement du formulaire
        if ($request->isMethod('POST')) {
            // Vérifier le token CSRF
            $submittedToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('register', $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('app_register');
            }

            $type = $request->request->get('type');
            
            if ($type === 'student') {
                return $this->registerStudent($request, $passwordHasher, $entityManager);
            } elseif ($type === 'company') {
                return $this->registerCompany($request, $passwordHasher, $entityManager);
            }
        }

        return $this->render('registration/register.html.twig');
    }

    private function registerStudent(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager
    ): Response
    {
        // Récupération des données
        $email = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');
        $firstName = trim($request->request->get('firstName', ''));
        $lastName = trim($request->request->get('lastName', ''));
        $skills = $request->request->all('skills') ?? [];
        $expectedLocation = trim($request->request->get('expectedLocation', ''));
        $expectedDuration = $request->request->get('expectedDuration', 6);
        $bio = trim($request->request->get('bio', ''));

        // Validation
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $this->addFlash('error', 'Please complete all required fields.');
            return $this->redirectToRoute('app_register');
        }

        // Vérifier si l'email existe déjà
        $existingUser = $entityManager->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $this->addFlash('error', 'This email is already registered.');
            return $this->redirectToRoute('app_register');
        }

        // Création de l'étudiant
        $student = new Student();
        $student->setEmail($email);
        $student->setPassword($passwordHasher->hashPassword($student, $password));
        $student->setRoles(['ROLE_STUDENT']);
        $student->setFirstName($firstName);
        $student->setLastName($lastName);
        
        // Nettoyer les skills (enlever les valeurs vides)
        $cleanSkills = array_filter($skills, fn($skill) => !empty(trim($skill)));
        $student->setSkills($cleanSkills);
        
        $student->setExpectedLocation($expectedLocation ?: null);
        $student->setExpectedDuration((int)$expectedDuration);
        $student->setBio($bio ?: null);

        try {
            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Account created successfully! You can now log in.');
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while creating your account. Please try again.');
            return $this->redirectToRoute('app_register');
        }
    }

    private function registerCompany(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager
    ): Response
    {
        // Récupération des données
        $email = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');
        $companyName = trim($request->request->get('companyName', ''));
        $industry = $request->request->get('industry', '');
        $location = trim($request->request->get('location', ''));
        $description = trim($request->request->get('description', ''));

        // Validation
        if (empty($email) || empty($password) || empty($companyName) || empty($industry) || empty($location)) {
            $this->addFlash('error', 'Please complete all required fields.');
            return $this->redirectToRoute('app_register');
        }

        // Vérifier si l'email existe déjà
        $existingUser = $entityManager->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $this->addFlash('error', 'This email is already registered.');
            return $this->redirectToRoute('app_register');
        }

        // Création de l'entreprise
        $company = new Company();
        $company->setEmail($email);
        $company->setPassword($passwordHasher->hashPassword($company, $password));
        $company->setRoles(['ROLE_COMPANY']);
        $company->setCompanyName($companyName);
        $company->setIndustry($industry);
        $company->setLocation($location);
        $company->setDescription($description ?: null);
        $company->setIsVerified(true); // Auto-vérification

        try {
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Company account created successfully! You can now log in.');
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while creating your account. Please try again.');
            return $this->redirectToRoute('app_register');
        }
    }
}