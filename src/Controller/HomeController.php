<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if ($this->getUser()) {
            $user = $this->getUser();
            
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            } elseif (in_array('ROLE_COMPANY', $user->getRoles())) {
                return $this->redirectToRoute('company_dashboard');
            } elseif (in_array('ROLE_STUDENT', $user->getRoles())) {
                return $this->redirectToRoute('student_dashboard');
            }
        }

        return $this->redirectToRoute('app_login');
    }
        
    
}
