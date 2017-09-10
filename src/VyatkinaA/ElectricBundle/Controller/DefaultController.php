<?php

namespace VyatkinaA\ElectricBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VyatkinaA\ElectricBundle\Entity\Results;
use VyatkinaA\ElectricBundle\Entity\Users;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VyatkinaAElectricBundle:Default:index.html.twig');
    }

    public function checkAction(Request $request){
        if($request != null && $request->request->get('id')){
            $id = $request->request->get('id');
            $arrData = $this->calcFieldAction($id);
            $step = $request->request->get('step') + 1;

        return new JsonResponse(['fields' => $arrData, 'step' => $step]);
    }
        return $this->json(array('er' => 'er'));//todo add exception
    }

    private function calcFieldAction($id){
        $check = function($val) {
            if ($val <= 55 && $val > 0 && (strpbrk($val, '06') === false)) {
                return true;
            }
            return false;
        };

        $result = [];

        if(!$check($id)) die('error: wrong id');
        $temp = str_split($id);

//main diagonal
        $item = ($temp[0]+1).($temp[1]+1);
        if($check($item)) array_push($result, $item);
        $item = ($temp[0]-1).($temp[1]-1);
        if($check($item)) array_push($result, $item);
//secondary diagonal
        $item = ($temp[0]-1).($temp[1]+1);
        if($check($item)) array_push($result, $item);
        $item = ($temp[0]+1).($temp[1]-1);
        if($check($item)) array_push($result, $item);
//vertical
        $item = ($temp[0]+1).$temp[1];
        if($check($item)) array_push($result, $item);
        $item = ($temp[0]-1).$temp[1];
        if($check($item)) array_push($result, $item);
//horizontal
        $item = $temp[0].($temp[1]+1);
        if($check($item)) array_push($result, $item);
        $item = $temp[0].($temp[1]-1);
        if($check($item)) array_push($result, $item);
        return $result;
    }

    public function jokerAction(Request $request){
        if($request != null && $request->request->get('fields_on')) {
            if (rand(1, 100) < 5) {
                $fields_on = $request->request->get('fields_on');
                $rand_key = array_rand($request->request->get('fields_on'), 1);
                $joker = $fields_on[$rand_key];

            return new JsonResponse(['answer' => true, 'joker' => $joker]);
            }
        }
        return new JsonResponse(['answer' => false]);
    }

    public function saveAction(Request $request){
        $step = $request->request->get('step');
        if($request != null && $request->request->get('username')) {
            $user = new Users();
            $user->setUsername($request->request->get('username'));

            $result = new Results();
            $result->setResult($step);
            $result->setUserId($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->persist($result);
            $em->flush();
            return new JsonResponse(['answer' => true]);
        }else{
            return $this->render('VyatkinaAElectricBundle:Default:save.html.twig',
                ['step' => $step]);
        }
        return new JsonResponse(['answer' => false]);
    }

    public function bestAction(){
        $results = $this->getDoctrine()
                ->getRepository(Results::class)
                ->findBy([],['result' => 'ASC'], 10);


        return $this->render('VyatkinaAElectricBundle:Default:best.html.twig',
            ['results' => $results]);
        }
}
