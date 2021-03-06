<?php

namespace Translatable\Fixture\Template;

/**
 * @MappedSuperclass
 */
class ArticleTemplate
{
    /**
     * @gedmo:Translatable
     * @Column(name="title", type="string", length=128)
     */
    private $title;

    /**
     * @gedmo:Translatable
     * @Column(name="content", type="text")
     */
    private $content;
    
    /**
     * Used locale to override Translation listener`s locale
     * @gedmo:Locale
     */
    protected $locale;
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}