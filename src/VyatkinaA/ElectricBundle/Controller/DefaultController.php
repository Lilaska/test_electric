<?php

namespace VyatkinaA\ElectricBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VyatkinaA\ElectricBundle\Entity\Results;
use VyatkinaA\ElectricBundle\Entity\Users;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    private $start_counter = [0,0,0,0,0];
    private $start_step = 0;

    public function indexAction()
    {
        return $this->render('VyatkinaAElectricBundle:Default:index.html.twig',[
            'counter'=> $this->start_counter,
            'current_step' => $this->start_step]
        );
    }

    public function checkAction(Request $request){
        if($request != null && $request->request->get('id')){
            $id = $request->request->get('id');
            $arrData = $this->calcFieldAction($id);
            $step = $request->request->get('step')+1;
            $counter = array_pad(str_split($step), -5, 0);
            $counter_template = $this->renderView('VyatkinaAElectricBundle:Default:counter.html.twig',[
                'counter' => $counter,
                'current_step' => $step
            ]);

        return new JsonResponse(['fields' => $arrData, 'step' => $step, 'counter_template' => $counter_template]);
    }
        return new JsonResponse([], Response::HTTP_BAD_REQUEST );
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
            if (rand(1, 25) == 1) {
                $fields_on = $request->request->get('fields_on');
                $rand_key = array_rand($request->request->get('fields_on'), 1);
                $joker = $fields_on[$rand_key];

            return new JsonResponse(['answer' => true, 'joker' => $joker]);
            }
        }
        return new JsonResponse(['answer' => false]);
    }

    public function saveAction(Request $request){
        $response = new JsonResponse();

        $step = $request->request->get('step');
        if($request != null && $request->request->get('username')) {
            $em = $this->getDoctrine()->getManager();
            if (!$request->cookies->has('user_data')) {
                $user = new Users();
                $user->setUsername($request->request->get('username'));
                $em->persist($user);
                $response->headers->setCookie(new Cookie('user_data', $user->getId()));
            }else{
                $user = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->find($request->cookies->get('user_data'));
                if($user->getUsername() !== $request->request->get('username')){
                    $user->setUsername($request->request->get('username'));
                }
            }
            $result = new Results();
            $result->setResult($step);
            $result->setUserId($user);
            $em->persist($result);
            $em->flush();
            $response->setContent(json_encode(['answer' => true]));
        }else{
            $username = '';
            if ($request->cookies->has('user_data'))
            {
                $user_id = $request->cookies->get('user_data');
                $user = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->find($user_id);
                if($user){
                    $username = $user->getUsername();
                }
            }
            $template = $this->renderView('VyatkinaAElectricBundle:Default:save.html.twig',
                ['step' => $step, 'username' => $username]);
           $response->setContent(json_encode(['answer' => true, 'save_template' => $template]));
        }
        return $response;
    }

    public function bestAction(){
        $results = $this->getDoctrine()
                ->getRepository(Results::class)
                ->findBy([],['result' => 'ASC'], 10);


        return $this->render('VyatkinaAElectricBundle:Default:best.html.twig',
            ['results' => $results]);
        }

        public function newAction(Request $request){

            $counter_template = $this->renderView('VyatkinaAElectricBundle:Default:counter.html.twig',[
                'counter' => $this->start_counter,
                'current_step' => $this->start_step
            ]);
            $field_template = $this->renderView('VyatkinaAElectricBundle:Default:field.html.twig');

            return new JsonResponse([
                'field_template' => $field_template,
                'counter_template' => $counter_template
            ], Response::HTTP_OK);
        }


}
