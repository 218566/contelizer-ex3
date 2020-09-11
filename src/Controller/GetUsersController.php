<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetUsersController extends AbstractController
{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/users", name="get_users")
     */
    public function getUsers() {

        $response = $this->client->request(
            'GET',
            'https://gorest.co.in/public-api/users',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );
        $content = $response->getContent();
        $decodedContent = json_decode($content);
        $usersList = $decodedContent->data;

        return $this->render('user-list.html.twig', [
            'users' => $usersList,
        ]);
    }

    /**
     * @Route("/search", name="search_users")
     */
    public function searchUsers(Request $request)
    {
        // dump($request->query->all());
        // dump($request);
        // die;



        $response = $this->client->request(
            'GET',
            'https://gorest.co.in/public-api/users',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );
        $content = $response->getContent();
        $decodedContent = json_decode($content);
        $usersList = $decodedContent->data;


        $parameters = $request->query->all();

        if (isset($parameters['form']['name'])) {
            $name = $parameters['form']['name'];

            foreach ($usersList as $user) {
                if ($user->name == $name) {
                    $searchedUser = $user;
                }
            }
            dump($searchedUser);
            die;
        }
        else {
            echo "Nie wyszukano użytkownika";
        }
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('search', SubmitType::class, ['label' => 'Search'])
            ->setAction('/search')
            ->setMethod('GET')
            ->getForm();

        return $this->render('form/search-form.html.twig', [
            'search_form' => $form->createView(),
        ]);
        //dump($searchedUser);
        //die;

    }

}