<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Model\Out\User\UserOut;
use App\Model\Out\User\UserListOut;
use App\Model\In\User\UserCreateIn;
use App\Model\In\User\UserUpdateIn;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class UserController extends AbstractController
{
    /**
     * @var AutoMapperInterface $autoMapper
     */
    private $autoMapper;

    /**
     * @var ObjectRepository $userRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @param AutoMapperInterface $autoMapper
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(AutoMapperInterface $autoMapper, EntityManagerInterface $entityManager)
    {
        $this->autoMapper = $autoMapper;
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @param UserCreateIn $userCreateIn
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    #[ParamConverter("userCreateIn", converter: "fos_rest.request_body")]
    public function createUser(UserCreateIn $userCreateIn, ValidatorInterface $validator): JsonResponse
    {
        $createUser = $this->autoMapper->mapToObject($userCreateIn, new User());

        $errors = $validator->validate($createUser);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->persist($createUser);
        $this->entityManager->flush();

        return new JsonResponse($createUser->getId());
    }

    /**
     * @param UserUpdateIn $userUpdateIn
     * @param int $id
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/user/{id}', name: 'update_user', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    #[ParamConverter("userUpdateIn", converter: "fos_rest.request_body")]
    public function updateUser(UserUpdateIn $userUpdateIn, ValidatorInterface $validator, $id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localUser = $this->userRepository->find($id);

        if(!$localUser) {
            return new JsonResponse(["status" => false, "message" => "user not found"]);
        }

        // if (!$localUser) {
        //     throw $this->createNotFoundException(
        //         'User not found for id '.$id
        //     );
        // }

        $updateUser = $this->autoMapper->mapToObject($userUpdateIn, $localUser);

        $errors = $validator->validate($updateUser);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->persist($updateUser);
        $this->entityManager->flush();
        return new JsonResponse($updateUser->getId());
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/user/{id}', name: 'id_user', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getUserById($id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localUser = $this->userRepository->find($id);

        if(!$localUser) {
            return new JsonResponse(["status" => false, "message" => "user not found"]);
        }

        // if (!$localUser) {
        //     throw $this->createNotFoundException(
        //         'User not found for id '.$id
        //     );
        // }

        return new JsonResponse($this->autoMapper->map($localUser, UserOut::class));
    }

    /**
     * @return JsonResponse
     */
    #[Route('/api/user/list', name: 'list_user', methods: ['GET'])]
    public function getUserList(): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $listUser = $this->userRepository->findAll();

        if(!$listUser) {
            return new JsonResponse(["status" => false, "message" => "user not found"]);
        }

        // if (!$localUser) {
        //     throw $this->createNotFoundException(
        //         'User not found for id '.$id
        //     );
        // }

        return new JsonResponse($this->autoMapper->mapMultiple($listUser, UserListOut::class));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/user/{id}', name: 'delete_user', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteUser($id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localUser = $this->userRepository->find($id);

        if(!$localUser) {
            return new JsonResponse(["status" => false, "message" => "user not found"]);
        }

        // if (!$localUser) {
        //     throw $this->createNotFoundException(
        //         'User not found for id '.$id
        //     );
        // }

        $this->entityManager->remove($localUser);
        $this->entityManager->flush();

        return new JsonResponse($localUser->getId());
    }
}
