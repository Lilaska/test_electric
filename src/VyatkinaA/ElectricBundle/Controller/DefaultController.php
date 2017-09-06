<?php

namespace VyatkinaA\ElectricBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VyatkinaAElectricBundle:Default:index.html.twig');
    }
}
