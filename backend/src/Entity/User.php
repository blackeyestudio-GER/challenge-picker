<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'IDX_1483A5E9_OAUTH', columns: ['oauth_provider', 'oauth_id'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $uuid;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $username;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $oauthProvider = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $oauthId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    private ?string $discordId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $discordUsername = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $discordAvatar = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    private ?string $twitchId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $twitchUsername = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $twitchAvatar = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $theme = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $activeDesignSetId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $refreshTokenExpiresAt = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Playthrough::class)]
    private Collection $playthroughs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: GameCategoryVote::class)]
    private Collection $categoryVotes;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->playthroughs = new ArrayCollection();
        $this->categoryVotes = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOauthProvider(): ?string
    {
        return $this->oauthProvider;
    }

    public function setOauthProvider(?string $oauthProvider): self
    {
        $this->oauthProvider = $oauthProvider;

        return $this;
    }

    public function getOauthId(): ?string
    {
        return $this->oauthId;
    }

    public function setOauthId(?string $oauthId): self
    {
        $this->oauthId = $oauthId;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function isModerator(): bool
    {
        return in_array('ROLE_MOD', $this->roles) || $this->isAdmin();
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_values(array_filter($this->roles, fn ($r) => $r !== $role));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(?string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getDiscordUsername(): ?string
    {
        return $this->discordUsername;
    }

    public function setDiscordUsername(?string $discordUsername): self
    {
        $this->discordUsername = $discordUsername;

        return $this;
    }

    public function getDiscordAvatar(): ?string
    {
        return $this->discordAvatar;
    }

    public function setDiscordAvatar(?string $discordAvatar): self
    {
        $this->discordAvatar = $discordAvatar;

        return $this;
    }

    public function getTwitchId(): ?string
    {
        return $this->twitchId;
    }

    public function setTwitchId(?string $twitchId): self
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getTwitchUsername(): ?string
    {
        return $this->twitchUsername;
    }

    public function setTwitchUsername(?string $twitchUsername): self
    {
        $this->twitchUsername = $twitchUsername;

        return $this;
    }

    public function getTwitchAvatar(): ?string
    {
        return $this->twitchAvatar;
    }

    public function setTwitchAvatar(?string $twitchAvatar): self
    {
        $this->twitchAvatar = $twitchAvatar;

        return $this;
    }

    /**
     * Check if Discord is connected.
     */
    public function hasDiscordConnected(): bool
    {
        return $this->discordId !== null;
    }

    /**
     * Check if Twitch is connected.
     */
    public function hasTwitchConnected(): bool
    {
        return $this->twitchId !== null;
    }

    /**
     * Check if user is using OAuth authentication.
     */
    public function isOAuthUser(): bool
    {
        return $this->oauthProvider !== null;
    }

    /**
     * Check if user is using email/password authentication.
     */
    public function isPasswordUser(): bool
    {
        return $this->password !== null;
    }

    /**
     * @return Collection<int, Playthrough>
     */
    public function getPlaythroughs(): Collection
    {
        return $this->playthroughs;
    }

    public function addPlaythrough(Playthrough $playthrough): static
    {
        if (!$this->playthroughs->contains($playthrough)) {
            $this->playthroughs->add($playthrough);
            $playthrough->setUser($this);
        }

        return $this;
    }

    public function removePlaythrough(Playthrough $playthrough): static
    {
        if ($this->playthroughs->removeElement($playthrough)) {
            // set the owning side to null (unless already changed)
            if ($playthrough->getUser() === $this) {
                $playthrough->setUser(null);
            }
        }

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getActiveDesignSetId(): ?int
    {
        return $this->activeDesignSetId;
    }

    public function setActiveDesignSetId(?int $activeDesignSetId): self
    {
        $this->activeDesignSetId = $activeDesignSetId;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(?\DateTimeImmutable $refreshTokenExpiresAt): self
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;

        return $this;
    }
}
