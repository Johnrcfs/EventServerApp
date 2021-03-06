<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AudienceRepository")
 * @ORM\Table(name="eventic_audience")
 * @Assert\Callback({"App\Validation\Validator", "validate"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @Vich\Uploadable
 */
class Audience {

    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="audiences")
     */
    private $events;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="audience_image", fileNameProperty="imageName", size="imageSize", mimeType="imageMimeType", originalName="imageOriginalName", dimensions="imageDimensions")
     * @Assert\File(
     *     maxSize = "2M",
     *     mimeTypes = {"image/jpeg", "image/jpg", "image/gif", "image/png"},
     *     mimeTypesMessage = "The file should be an image"
     *     )
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var integer
     */
    private $imageSize;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $imageMimeType;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $imageOriginalName;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $imageDimensions;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull
     */
    private $hidden = false;

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct() {
        $this->events = new ArrayCollection();
    }

    public function __call($method, $arguments) {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), $method);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->translate()->getName();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     */
    public function setImageFile(File $imageFile = null) {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
// It is required that at least one field changes if you are using doctrine
// otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile() {
        return $this->imageFile;
    }

    public function getImagePath() {
        return 'uploads/audiences/' . $this->imageName;
    }

    public function getImagePlaceholder($size = "default") {
        if ($size == "small") {
            return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABGCAMAAABG8BK2AAABelBMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhEnoGGTAAAAfXRSTlMAAQIDBAUGBwgJCw0ODxAREhMUFRkaHR4fICEiIyQmKCsuMjM1Njk6PD0+P0BBQ0RFRklKT1JUV1hZXF9hYmRmZ2lsdHV3fH6AgoOFhoiJi4yOkZWYmp2gpauvsLK0t7m6vsDBw8fIzM7T1dfZ2tze4OLk6+3v8fP19/n7/VzZeAkAAAHKSURBVBgZ7cH5W0xRAMfhz50aI0M1ZCtZQ2nsZBcJGSlkXyclIlJI053v/647S3NPD91z7k88z7wvdXV1/5XmoQUF3vZ6xNdTVNWHFHHtVsiURzzevMK6iWeHDOPEc0UhPwY7iWdMNf0NxDWlKn8X8X1VVSfgHRz9uODPv7jUgpufqrgLtMyo6hpOllSxHtJLqsnhQhXPgbzCOnCwqLJeaJVhDAfTKmuDszIlsHdfZSm4J9NG7GVV5sG4TO3YS6nEBz7LtBcHwwrMAd9l6sLBhqKWzQAFmQ7j4rSWfQKKMvXgJCfpC+DL1I2bAWkRmJVpH44uSAl4KtNmHGWlDPTJ4Hs4aWy5KR2HZhlGsZc58fiXAq+A1wrbhqWmvlmtSMN2hbzETuqWwm4Dd7TCT2PllC/TJkhMqmo/NhIPtFreg8a8yg5h5YlWKzzKAF5OgVasDMhUHO7wKMkqgJVWGRbOJak6o4CHjRsKG2ykJHNyZMJX2dyz63uSRJhWzbc2AluHClrt/bEkazmSrUmy7MCM/izXhLUtk/qr4mUPO0e1pjcpbFxUhLkU0boUaSJBlHUFRbtKlPOysNRAhHey0U6EkYc2dlJXV/dv+w33zOpPevC2fgAAAABJRU5ErkJggg==";
        } else {
            return "data:image/png;base64,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";
        }
    }

    public function getUpdatedAt() {
        return $this->updatedAt == $this->createdAt ? null : $this->updatedAt;
    }

    public function getImageName() {
        return $this->imageName;
    }

    public function setImageName($imageName) {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize() {
        return $this->imageSize;
    }

    public function setImageSize($imageSize) {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getImageMimeType() {
        return $this->imageMimeType;
    }

    public function setImageMimeType($imageMimeType) {
        $this->imageMimeType = $imageMimeType;

        return $this;
    }

    public function getImageOriginalName() {
        return $this->imageOriginalName;
    }

    public function setImageOriginalName($imageOriginalName) {
        $this->imageOriginalName = $imageOriginalName;

        return $this;
    }

    public function getImageDimensions() {
        return $this->imageDimensions;
    }

    public function setImageDimensions($imageDimensions) {
        $this->imageDimensions = $imageDimensions;

        return $this;
    }

    public function getHidden() {
        return $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;

        return $this;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt() {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection {
        return $this->events;
    }

    public function addEvent(Event $event) {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addAudience($this);
        }

        return $this;
    }

    public function removeEvent(Event $event) {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            $event->removeAudience($this);
        }

        return $this;
    }

}
