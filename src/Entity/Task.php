<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    const STATUS = [
        'todo' => 0,
        'done' => 1
    ];

    const PRIORITY = [
        1 => 0,
        2 => 1,
        3 => 2,
        4 => 3,
        5 => 4
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::SMALLINT, options: ["default" => 0])]
    private ?int $status = 0;

    #[ORM\Column(type: Types::SMALLINT, options: ["default" => 0])]
    private ?int $priority = 0;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'tasks')]
    private ?self $subtask = null;

    #[ORM\OneToMany(mappedBy: 'subtask', targetEntity: self::class)]
    private Collection $tasks;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStringStatus()
    {
        return array_flip(self::STATUS)[$this->getStatus()] ?? "undefined";
    }

    /**
     * @return array
     */
    public function getStatusList(): array
    {
        return array_keys(self::STATUS);
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getStringPriority(): string
    {
        return array_flip(self::PRIORITY)[$this->getPriority()] ?? "undefined";
    }

    /**
     * @return array
     */
    public function getPriorityList(): array
    {
        return array_keys(self::PRIORITY);
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSubtask(): ?self
    {
        return $this->subtask;
    }

    public function setSubtask(?self $subtask): static
    {
        $this->subtask = $subtask;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(self $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setSubtask($this);
        }

        return $this;
    }

    public function removeTask(self $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getSubtask() === $this) {
                $task->setSubtask(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
