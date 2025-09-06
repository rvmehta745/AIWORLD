<?php

return [

    /**
     * What attributes should be used to build the slug.
     */
    'source' => null,

    /**
     * Slug separator.
     */
    'separator' => '-',

    /**
     * Slugging method.
     *
     * Options: 'default' or a custom method name.
     */
    'method' => null,

    /**
     * Should slugs be unique?
     */
    'unique' => true,

    /**
     * Update slug on model update?
     */
    'onUpdate' => true,
    'maxLength' => 255,
    'maxLengthKeepWords' => true, 
    'slugEngineOptions' => [],
    'reserved' => null,
    'includeTrashed' => false,
];
