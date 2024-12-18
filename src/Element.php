<?php

namespace HTML;

class Element
{

    public readonly Tag        $tag;
    public readonly Attributes $attributes;

    /**
     * @param string  $tag
     * @param array   $attributes
     */
    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = new Tag($tag);

        $this->attributes = new Attributes($attributes);
    }

}
