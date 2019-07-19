<?php declare(strict_types = 1);

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="steam_app")
 **/
class SteamApp
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $steam_appid;

    /** @ORM\Column(type="string") **/
    protected $name;

    /** @ORM\Column(type="simple_array") **/
    protected $categories;

    /** @ORM\Column(type="string") **/
    protected $header_image;

    /** @ORM\Column(type="boolean") **/
    protected $is_free;

    /** @ORM\Column(type="json") **/
    protected $platforms;

    public function getValues(): array
    {
        return [
            'steam_appid'  => $this->steam_appid,
            'name'         => $this->name,
            'categories'   => $this->categories,
            'header_image' => $this->header_image,
            'is_free'      => $this->is_free,
            'platforms'    => $this->platforms
        ];
    }

    public function setValues($values)
    {
        $this->steam_appid  = $values['steam_appid'];
        $this->name         = $values['name'];
        $this->categories   = $values['categories'];
        $this->header_image = $values['header_image'];
        $this->is_free      = $values['is_free'];
        $this->platforms    = $values['platforms'];
    }
}