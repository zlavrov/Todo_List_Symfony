<?php

namespace App;

use DateTime;

use App\Entity\User;
use App\Model\In\User\UserCreateIn;
use App\Model\In\User\UserUpdateIn;
use App\Model\Out\User\UserListOut;
use App\Model\Out\User\UserOut;
use App\Model\Out\User\UserShortOut;

use App\Entity\Task;
use App\Model\In\Task\TaskCreateIn;
use App\Model\In\Task\TaskUpdateIn;
use App\Model\Out\Task\TaskListOut;
use App\Model\Out\Task\TaskOut;

use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use AutoMapperPlus\Configuration\Options;
use AutoMapperPlus\MappingOperation\Operation;
use AutoMapperPlus\Configuration\MappingInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AutoMapperConfig implements AutoMapperConfiguratorInterface {

    private $entityManager;

    private $autoMapper;

    private $userPasswordHasher;

    public function __construct(AutoMapperInterface $autoMapper, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->autoMapper = $autoMapper;
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function configure(AutoMapperConfigInterface $config): void
    {
        $this->configureTask($config);
        $this->configureUser($config);
    }

    public function configureUser(AutoMapperConfigInterface $config): void
    {            
        // UserCreateIn model -> User entity
        $config->registerMapping(UserCreateIn::class, User::class)
        ->forMember('password', function (UserCreateIn $userCreateIn) {
            return $this->userPasswordHasher->hashPassword(new User(), $userCreateIn->password);
        });

        // UserUpdateIn model -> User entity
        $config->registerMapping(UserUpdateIn::class, User::class)
        ->forMember('password', function (UserUpdateIn $userUpdateIn) {
            return $this->userPasswordHasher->hashPassword(new User(), $userUpdateIn->password);
        });

        // User entity -> UserOut model
        $config->registerMapping(User::class, UserOut::class);

        // User entity -> UserListOut model
        $config->registerMapping(User::class, UserListOut::class);

        // User entity -> UserShortOut model
        $config->registerMapping(User::class, UserShortOut::class);
    }

    public function configureTask(AutoMapperConfigInterface $config): void
    {            
        // TaskCreateIn model -> Task entity
        $config->registerMapping(TaskCreateIn::class, Task::class)
        ->forMember('status', function (TaskCreateIn $taskCreateIn) {
            return Task::STATUS[$taskCreateIn->status];
        })
        ->forMember('priority', function (TaskCreateIn $taskCreateIn) {
            return Task::PRIORITY[$taskCreateIn->priority];
        })
        ->forMember('userId', function (TaskCreateIn $taskCreateIn) {
            return $this->entityManager->find(User::class, $taskCreateIn->userId);
        })
        ->forMember('subtask', function (TaskCreateIn $taskCreateIn) {
            return $this->entityManager->find(Task::class, $taskCreateIn->subtask);
        });

        // TaskUpdateIn model -> Task entity
        $config->registerMapping(TaskUpdateIn::class, Task::class)
        ->forMember('status', function (TaskUpdateIn $taskUpdateIn) {
            return Task::STATUS[$taskUpdateIn->status];
        })
        ->forMember('priority', function (TaskUpdateIn $taskUpdateIn) {
            return Task::PRIORITY[$taskUpdateIn->priority];
        })
        ->forMember('userId', function (TaskUpdateIn $taskUpdateIn) {
            return $this->entityManager->find(User::class, $taskUpdateIn->userId);
        })
        ->forMember('subtask', function (TaskUpdateIn $taskUpdateIn) {
            return $this->entityManager->find(Task::class, $taskUpdateIn->subtask);
        });

        // Task entity -> TaskOut model
        $config->registerMapping(Task::class, TaskOut::class)
        ->forMember('status', function (Task $task) {
            return $task->getStringStatus();
        })
        ->forMember('priority', function (Task $task) {
            return $task->getStringPriority();
        })
        ->forMember('userId', Operation::mapTo(UserShortOut::class))
        ->forMember('subtask', Operation::mapTo(TaskOut::class));

        // Task entity -> TaskListOut model
        $config->registerMapping(Task::class, TaskListOut::class)
        ->forMember('status', function (Task $task) {
            return $task->getStringStatus();
        })
        ->forMember('priority', function (Task $task) {
            return $task->getStringPriority();
        })
        ->forMember('userId', Operation::mapTo(UserShortOut::class))
        ->forMember('subtask', Operation::mapTo(TaskOut::class));
    }
}
