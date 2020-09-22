<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Sodium\add;

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

        $form = $this->createFormBuilder()
            ->add('search_user',SubmitType::class,['label' => 'Search'])
            ->setAction('/search')
            ->add('add_user',SubmitType::class,['label' => 'Add'])
            ->setAction('/add')
            ->getForm();

        return $this->render('user-list.html.twig', [
            'users' => $usersList,
            'main_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search", name="search_users")
     */
    public function searchUsers(Request $request) {

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
        $searchedUser = NULL;
        if (isset($parameters['form']['name'])) {
            $name = $parameters['form']['name'];

            foreach ($usersList as $user) {
                if ($user->name == $name) {
                    $searchedUser = $user;
                }
            }
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('search', SubmitType::class, ['label' => 'Search'])
            ->setAction('/search')
            ->setMethod('GET')
            ->getForm();

        return $this->render('form/search-form.html.twig', [
            'search_form' => $form->createView(),
            'searchedUser' => $searchedUser,
        ]);
    }

    /**
     * @Route("/add", name="add_users")
     */
    public function addUsers(Request $request) {

        $form = $this->createFormBuilder()
            ->add('name',TextType::class,['label' => 'Name'])
            ->add('gender',TextType::class,['label' => 'Gender'])
            ->add('email',TextType::class,['label' => 'Email'])
            ->add('status',TextType::class,['label' => 'Status'])
            ->add('add_user',SubmitType::class,['label' => 'Add User'])
            ->getForm();


        $form->handleRequest($request);
        $formData = $form->getData();

        $response = $this->client->request('POST', 'https://gorest.co.in/public-api/users', [
            'json' => ['name' => $formData['name'], 'gender' => $formData['gender'], 'email' => $formData['email'], 'status' => $formData['status']],
            'auth_bearer' => '3ae14269aa9534a3b7a89ad96423a8d21688fbdf294e4f9872b7120ef756f9f1',
        ]);

        $decodedPayload = $response->toArray();

        if($decodedPayload['code'] == 201) {
            echo "Wysłane do API: id = ".$decodedPayload['data']['id'];
        }

        return $this->render('form/add-form.html.twig', [
            'add_form' => $form->createView(),
            'form_data' => $formData,
        ]);
    }

    /**
     * @Route("/delete", name="delete_users")
     */
    public function deleteUsers() {

    }
}

