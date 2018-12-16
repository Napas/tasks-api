<?php

namespace App\Tasks;

use Symfony\Component\Validator\Constraints as Assert;

class TaskRequest
{
    /**
     * @var string
     *
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="100")
     */
    protected $name;

    /**
     * @var string|null
     *
     * @Assert\Type("string")
     * @Assert\Length(max="1024")
     */
    protected $description;

    /**
     * @var \DateTime|null
     *
     * @Assert\Type("DateTime")
     */
    protected $deadlineAt;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeadlineAt(): ?\DateTime
    {
        return $this->deadlineAt;
    }

    public function setDeadlineAt(\DateTime $deadlineAt): self
    {
        $this->deadlineAt = $deadlineAt;

        return $this;
    }
}
