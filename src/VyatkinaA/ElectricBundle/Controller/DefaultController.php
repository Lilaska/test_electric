<?php

namespace VyatkinaA\ElectricBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VyatkinaA\ElectricBundle\Entity\Results;
use VyatkinaA\ElectricBundle\Entity\Steps;
use VyatkinaA\ElectricBundle\Entity\Users;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    private $start_counter = [0, 0, 0, 0, 0];
    private $start_step = 0;
    private $start_fields_on = [];
    private $counter_size = 5;
    private $field_size = 5;
    private $joker_chance = 25; //1 k joker_chance
    private $user_cookie = 'user_data';
    private $game_cookie = 'game_step';
    private $best_limit = 15;

    public function indexAction(Request $request)
    {
        try {
            //get start params for new game
            $counter = $this->start_counter;
            $step = $this->start_step;
            $fields_on = $this->start_fields_on;
            //check step cookies
            if ($request->cookies->has($this->game_cookie)) {
                $game_id = $request->cookies->get($this->game_cookie);
                //get progress from db
                $game = $this->getDoctrine()
                    ->getRepository(Steps::class)
                    ->find($game_id);
                //if game found
                if ($game) {
                    $step = $game->getStep();
                    $counter = array_pad(str_split($step), -$this->counter_size, 0);
                    $fields_on = $game->getFieldsOn();
                } else {
                    throw new \Exception('Game #'.$game_id.' not found', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            return $this->render('VyatkinaAElectricBundle:Default:index.html.twig', [
                    'counter' => $counter,
                    'current_step' => $step,
                    'fields_on' => $fields_on
                ]
            );
        }catch (\Exception $ex){
            return new \HttpException($ex->getMessage(), $ex->getCode());
        }
    }

    public function checkAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
        try {
            $response = new JsonResponse();

            if ($request->request->get('id')) {
                $id = $request->request->get('id');
                $arrData = $this->calcFieldAction($id);
                $step = $request->request->get('step') + 1;
                $counter = array_pad(str_split($step), -$this->counter_size, 0);
                $counter_template = $this->renderView('VyatkinaAElectricBundle:Default:counter.html.twig', [
                    'counter' => $counter,
                    'current_step' => $step
                ]);

                //check step cookies
                if ($request->cookies->has($this->game_cookie)) {
                    $game_id =$request->cookies->get($this->game_cookie);
                    //get progress from db
                    $game = $this->getDoctrine()
                        ->getRepository(Steps::class)
                        ->find($game_id);
                    //if game found
                    if (!$game) {
                        throw new \Exception('Game #'.$game_id.' not found', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $old_fields_on = $game->getFieldsOn();
                    $new_fields_on = $arrData;
                    $fields_save = array_merge(array_diff($old_fields_on, $new_fields_on), array_diff($new_fields_on, $old_fields_on));

                } else {
                    $game = new Steps();
                    $fields_save = $arrData;
                }
                $fields_save[] = $id;
                $game->setStep($step);
                $game->setFieldsOn($fields_save);
                $em = $this->getDoctrine()->getManager();
                $em->persist($game);
                $em->flush();
                $response->headers->setCookie(new Cookie($this->game_cookie, $game->getId()));
                $response->setContent(json_encode(['fields' => $arrData, 'step' => $step, 'counter_template' => $counter_template]));
                $response->setStatusCode(Response::HTTP_OK);
                return $response;
            }else{
                throw new \Exception('Not given parameter id', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }catch (\Exception $ex){
            return new JsonResponse(['error' => $ex->getMessage()], $ex->getCode());
        }
        } else {
            throw new \HttpException("Request must be is ajax", Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    private function calcFieldAction($id)
    {
        $check = function ($val) {
            $max_id = $this->field_size.$this->field_size;
            $wrong_id = '0'.($this->field_size + 1);
            if ($val <= $max_id && $val > 0 && (strpbrk($val, $wrong_id) === false)) {
                return true;
            }
            return false;
        };

        $result = [];

        if (!$check($id)) throw new \Exception('Given wrong id', Response::HTTP_INTERNAL_SERVER_ERROR);
        $temp = str_split($id);

//main diagonal
        $item = ($temp[0] + 1) . ($temp[1] + 1);
        if ($check($item)) array_push($result, $item);
        $item = ($temp[0] - 1) . ($temp[1] - 1);
        if ($check($item)) array_push($result, $item);
//secondary diagonal
        $item = ($temp[0] - 1) . ($temp[1] + 1);
        if ($check($item)) array_push($result, $item);
        $item = ($temp[0] + 1) . ($temp[1] - 1);
        if ($check($item)) array_push($result, $item);
//vertical
        $item = ($temp[0] + 1) . $temp[1];
        if ($check($item)) array_push($result, $item);
        $item = ($temp[0] - 1) . $temp[1];
        if ($check($item)) array_push($result, $item);
//horizontal
        $item = $temp[0] . ($temp[1] + 1);
        if ($check($item)) array_push($result, $item);
        $item = $temp[0] . ($temp[1] - 1);
        if ($check($item)) array_push($result, $item);
        return $result;
    }

    public function jokerAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                if ($request->request->get('fields_on')) {
                    if (rand(1, $this->joker_chance) == 1) {
                        $fields_on = $request->request->get('fields_on');
                        $rand_key = array_rand($request->request->get('fields_on'), 1);
                        $joker = $fields_on[$rand_key];
                        if ($request->cookies->has($this->game_cookie)) {
                            $game_id = $request->cookies->get($this->game_cookie);
                            $game = $this->getDoctrine()
                                ->getRepository(Steps::class)
                                ->find($game_id);
                            if ($game) {
                                $fields_save = $game->getFieldsOn();
                                unset($fields_save[array_search($joker, $fields_save)]);
                                $game->setFieldsOn($fields_save);
                                $em = $this->getDoctrine()->getManager();
                                $em->persist($game);
                                $em->flush();
                            }else{
                                throw new \Exception('Game #'.$game_id.' not found', Response::HTTP_UNPROCESSABLE_ENTITY);
                            }
                        }else{
                            throw new \Exception('Cookie game_step not found', Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        return new JsonResponse(['answer' => true, 'joker' => $joker], Response::HTTP_OK);
                    }else{
                        return new JsonResponse(['answer' => false], Response::HTTP_OK);
                    }
                }
                throw $this->createNotFoundException('Not given parameter fields_on');
            }catch (\Exception $ex){
                return new JsonResponse(['error' => $ex->getMessage()], $ex->getCode());
            }
        } else {
            throw new \HttpException("Request must be is ajax", Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    public function saveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {

                $response = new JsonResponse();
                if(!$request->request->has('step')){
                    throw $this->createNotFoundException('Not given parameter step');
                }

                $step = $request->request->get('step');
                if ($request->request->get('username')) {
                    $em = $this->getDoctrine()->getManager();
                    if (!$request->cookies->has($this->user_cookie)) {
                        $user = new Users();
                        $user->setUsername($request->request->get('username'));
                    } else {
                        $user = $this->getDoctrine()
                            ->getRepository(Users::class)
                            ->find($request->cookies->get($this->user_cookie));
                        if ($user->getUsername() !== $request->request->get('username')) {
                            $user->setUsername($request->request->get('username'));
                        }
                    }
                    $result = new Results();
                    $result->setResult($step);
                    $result->setUserId($user);
                    $em->persist($user);
                    $em->persist($result);
                    $em->flush();
                    $response->headers->setCookie(new Cookie($this->user_cookie, $user->getId()));
                    $response->setContent(json_encode(['answer' => true]));
                } else {
                    $username = '';
                    if ($request->cookies->has($this->user_cookie)) {
                        $user_id = $request->cookies->get($this->user_cookie);
                        $user = $this->getDoctrine()
                            ->getRepository(Users::class)
                            ->find($user_id);
                        if ($user) {
                            $username = $user->getUsername();
                        } else {
                            throw new \Exception('User #'.$user_id.' not found', Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                    }
                    $template = $this->renderView('VyatkinaAElectricBundle:Default:save.html.twig',
                        ['step' => $step, 'username' => $username]);
                    $response->setContent(json_encode(['answer' => true, 'save_template' => $template]));
                }
                return $response;
            } catch (\Exception $ex) {
                return new JsonResponse(['error' => $ex->getMessage()], $ex->getCode());
            }
        } else {
            throw new \HttpException("Request must be is ajax", Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    public function bestAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $results = $this->getDoctrine()
                ->getRepository(Results::class)
                ->findBy([], ['result' => 'ASC'], $this->best_limit);


            return $this->render('VyatkinaAElectricBundle:Default:best.html.twig',
                ['results' => $results]);
        } else {
            throw new \HttpException("Request must be is ajax", Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $response = new JsonResponse();
                if ($request->cookies->has($this->game_cookie)) {
                    $game_id = $request->cookies->get($this->game_cookie);
                    $game = $this->getDoctrine()
                        ->getRepository(Steps::class)
                        ->find($game_id);
                    if ($game) {
                        $em = $this->getDoctrine()->getManager();
                        $em->remove($game);
                        $em->flush();
                    } else {
                        throw $this->createNotFoundException('Game #' . $game_id . 'not found');
                    }
                }
                $counter_template = $this->renderView('VyatkinaAElectricBundle:Default:counter.html.twig', [
                    'counter' => $this->start_counter,
                    'current_step' => $this->start_step
                ]);
                $field_template = $this->renderView('VyatkinaAElectricBundle:Default:field.html.twig');

                $response->setContent(json_encode([
                    'field_template' => $field_template,
                    'counter_template' => $counter_template
                ]));
                $response->headers->setCookie(new Cookie($this->game_cookie, null, -1));
                $response->setStatusCode(Response::HTTP_OK);
                return $response;
            } catch (\Exception $ex) {
                return new JsonResponse(['error' => $ex->getMessage()], $ex->getCode());
            }
        } else {
            throw new \HttpException("Request must be is ajax", Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }


}
