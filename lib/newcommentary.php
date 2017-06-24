<?php

class Commentary {
    public $section = 'village';
    public $amt = 25;
    public $options = [
        'enable_ooc' => true,
        'dyslexic_friendly' => true,
        'message' => 'Converse with fellow Xythenians:',
        'muted' => fales,
        'json' => false,
    ];
    public function __construct(string $section, int $amt = 25, array $options = [])
    {
        $this->section = $section;
        $this->amt = $amt
        if (!empty($options)) {
            $this->options = $options;
        }
    }
    public function postComment() :bool
    {

    }
    public function getComments() :array
    {

    }
    public function displayComments() :string
    {
        if ($this->options['json']) {
            //Display as JSON
        }
        else {
            //Display as a string
        }
    }
    public function 
}