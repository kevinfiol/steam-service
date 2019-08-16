<?php declare(strict_types = 1);

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="steam_category")
 **/
class SteamCategory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $category_id;

    /** @ORM\Column(type="string") **/
    protected $description;

    public function getValues(): array
    {
        return [
            'category_id' => $this->category_id,
            'description' => $this->description
        ];
    }

    public function setValues($values)
    {
        $this->category_id = $values['category_id'];
        $this->description = $values['description'];
    }
}