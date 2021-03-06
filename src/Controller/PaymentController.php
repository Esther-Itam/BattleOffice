<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;


class PaymentController extends AbstractController
{
    /**
     * @Route("/payment/{id}", name="payment")
     */

    public function index(Order $order): Response

    {

       $price=$order->getProduct()->getPrice();
       $emailClient=$order->getUser()->getEmail();
        return $this->render('payment/index.html.twig', [
            'price' => $price,
            'email' => $emailClient,
            'order' => $order,
        ]);
    }

    /**
     * @Route("/paymentNew/{id}", name="paymentNew")
    */
    public function paymentNew(Order $order)
    {
        \Stripe\Stripe::setApiKey('sk_test_51H2ZbWBOL1Ug5bIFqKwkRdtImBSxtHOIdClHA8RkNjdi0fJD7hKBvhbSLN3CYOOoG4NEaNg3UaJSD4sUQmFv8ArB00W293UMtm');

            try{ 
            // Token is created using Stripe Checkout or Elements!
            // Get the payment token ID submitted by the form:
            //  $token = $request->request->get('stripeToken'); /

            $charge = \Stripe\PaymentIntent::create([
            'amount' => intval($order->getProduct()->getPrice())*100,
            'currency' => 'eur',
            'description' => 'Example charge', 
            /* 'source' => $token, */
            
            ]);

            $apiId = $order->getApiId();
            $this->updateApi($apiId);    

            } catch (\Exception $e) {
                dd($e);
            } 


            return $this->render('landing_page/confirmation.html.twig', [

            ]);
    }


    public function updateApi($apiId)
    {
        $arrayStatus = [
            'status' => 'PAID'
        ];

        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api-commerce.simplon-roanne.com/order/'.$apiId.'/status', [
            'headers' => [
                'Accept' => 'application/json', //format de ce qu'on envoit
                'Content-Type'=> 'application/json', //format retour de la reponse
                'Authorization' => 'Bearer mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX'
            ],
            'body' => json_encode($arrayStatus)
        ]);
        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
       /*  $apiId = $content['order_id']; 
            dd($apiId); */
            //return $content;
    }

}