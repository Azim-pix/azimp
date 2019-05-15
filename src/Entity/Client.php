<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @ORM\Table(name="pxl_b2b_client")
 */
class Client implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(max=100)
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @Assert\NotBlank(groups = {"UserCreation"})
     * @Assert\Length(max=4096)
     * @Assert\Regex(
     *     pattern = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d$@$!%*#?&]{8,}$/",
     *     message = "error.password.format"
     * )
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ClientInfo", mappedBy="client", cascade={"persist", "remove"})
     */
    private $clientInfo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserMission", mappedBy="client")
     */
    private $userMission;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ClientMissionProposal", mappedBy="client")
     */
    private $clientMissionProposals;

    public function __construct()
    {
        $this->clientMissionProposals = new ArrayCollection();
    }

    public function __toString() {
        return $this->getCompany();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function eraseCredentials(){
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function getUsername(){
        return $this->email;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getClientInfo(): ?ClientInfo
    {
        return $this->clientInfo;
    }

    public function setClientInfo(?ClientInfo $clientInfo): self
    {
        $this->clientInfo = $clientInfo;

        // set (or unset) the owning side of the relation if necessary
        $newClient = $clientInfo === null ? null : $this;
        if ($newClient !== $clientInfo->getClient()) {
            $clientInfo->setClient($newClient);
        }

        return $this;
    }

        public function getUserMission(): ?Collection
    {
        return $this->userMission;
    }

    public function setUserMission(?UserMission $userMission): self
    {
        $this->userMission = $userMission;

        // set (or unset) the owning side of the relation if necessary
        $newClient = $userMission === null ? null : $this;
        if ($newClient !== $userMission->getClient()) {
            $userMission->setClient($newClient);
        }

        return $this;
    }

    /**
     * @return Collection|ClientMissionProposal[]
     */
    public function getClientMissionProposals(): Collection
    {
        return $this->clientMissionProposals;
    }

    public function addClientMissionProposal(ClientMissionProposal $clientMissionProposal): self
    {
        if (!$this->clientMissionProposals->contains($clientMissionProposal)) {
            $this->clientMissionProposals[] = $clientMissionProposal;
            $clientMissionProposal->setClient($this);
        }

        return $this;
    }

    public function removeClientMissionProposal(ClientMissionProposal $clientMissionProposal): self
    {
        if ($this->clientMissionProposals->contains($clientMissionProposal)) {
            $this->clientMissionProposals->removeElement($clientMissionProposal);
            // set the owning side to null (unless already changed)
            if ($clientMissionProposal->getClient() === $this) {
                $clientMissionProposal->setClient(null);
            }
        }

        return $this;
    }



}