<?php

namespace App\Controller\Api;

use DateTime;
use App\Entity\Task;
use App\Model\Out\Task\TaskOut;
use App\Model\Out\Task\TaskListOut;
use App\Model\In\Task\TaskCreateIn;
use App\Model\In\Task\TaskUpdateIn;
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

class TaskController extends AbstractController
{
    /**
     * @var AutoMapperInterface $autoMapper
     */
    private $autoMapper;

    /**
     * @var ObjectRepository $taskRepository
     */
    private $taskRepository;

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
        $this->taskRepository = $entityManager->getRepository(Task::class);
    }

    /**
     * @param TaskCreateIn $taskCreateIn
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/task', name: 'create_task', methods: ['POST'])]
    #[ParamConverter("taskCreateIn", converter: "fos_rest.request_body")]
    public function createTask(TaskCreateIn $taskCreateIn, ValidatorInterface $validator): JsonResponse
    {

        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $task = $this->autoMapper->mapToObject($taskCreateIn, new Task());

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse($task->getId());
    }

    /**
     * @param TaskUpdateIn $taskUpdateIn
     * @param int $id
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/task/{id}', name: 'update_task', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    #[ParamConverter("taskUpdateIn", converter: "fos_rest.request_body")]
    public function updateTask(TaskUpdateIn $taskUpdateIn, ValidatorInterface $validator, $id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localTask = $this->taskRepository->find($id);

        if(!$localTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        if($localTask->getUserId()->getId() !== $user->getId() || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(["status" => false, "message" => "permission denied"]);
        }

        $task = $this->autoMapper->mapToObject($taskUpdateIn, $localTask);

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return new JsonResponse($task->getId());
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/task/{id}', name: 'id_task', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getTaskById($id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localTask = $this->taskRepository->find($id);

        if(!$localTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        if($localTask->getUserId()->getId() == $user->getId() || in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse($this->autoMapper->map($localTask, TaskOut::class));
        } else {
            return new JsonResponse(["status" => false, "message" => "permission denied"]);
        }

    }

    /**
     * @return JsonResponse
     */
    #[Route('/api/task/list', name: 'list_task', methods: ['GET'])]
    public function getTaskList(): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        if(in_array('ROLE_ADMIN', $user->getRoles())) {
            $listTask = $this->taskRepository->findAll();
        } else {
            $listTask = $this->taskRepository->findBy(['userId' => $user->getId()]);
        }
        
        if(!$listTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        return new JsonResponse($this->autoMapper->mapMultiple($listTask, TaskListOut::class));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/task/{id}', name: 'delete_task', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteTask($id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localTask = $this->taskRepository->find($id);

        if(!$localTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        if($localTask->getUserId()->getId() == $user->getId() && $localTask->getStatus() == Task::STATUS['done']) {
            return new JsonResponse(["status" => false, "message" => "permission denied"]);
        }

        if(in_array('ROLE_ADMIN', $user->getRoles())) {

            $this->entityManager->remove($localTask);
            $this->entityManager->flush();
    
            return new JsonResponse($localTask->getId());
        }

        return new JsonResponse(["status" => false, "message" => "permission denied"]);
    }

    /**
     * @param TaskUpdateIn $taskUpdateIn
     * @param int $id
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/status-task/{id}', name: 'update_status_priority_task', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    #[ParamConverter("taskUpdateIn", converter: "fos_rest.request_body")]
    public function updateStatusPriorityTask(TaskUpdateIn $taskUpdateIn, ValidatorInterface $validator, $id): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $localTask = $this->taskRepository->find($id);

        if(!$localTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        if($taskUpdateIn->status) {
            $newStatus = Task::STATUS[$taskUpdateIn->status];

            $localTask->setStatus($newStatus);
            $localTask->setCompletedAt(new DateTime('now'));
        }

        if($taskUpdateIn->priority) {
            $newPriority = Task::PRIORITY[$taskUpdateIn->priority];

            $localTask->setPriority($newPriority);
        }

        $this->entityManager->persist($localTask);
        $this->entityManager->flush();
        return new JsonResponse($localTask->getId());
    }

    /**
     * @return JsonResponse
     */
    #[Route('/api/task/filter', name: 'list_filter_task', methods: ['GET'])]
    public function getTaskFilterList(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if(!$user) {
            return new JsonResponse(["status" => false, "message" => "user error auth"]);
        }

        $listTask = $this->taskRepository->TaskFilter($request->query->all());

        if(!$listTask) {
            return new JsonResponse(["status" => false, "message" => "task not found"]);
        }

        return new JsonResponse($this->autoMapper->mapMultiple($listTask, TaskListOut::class));
    }
}
