<?php

declare(strict_types=1);


return [

    /*
     * Factory
     */
    'factory'                    => Bpartner\ApiResponse\ResponseFactory::class,

    /*
     * Set wrapper for Data. Can be nullable
     */
    'wrapper'                    => 'data',
    'string_field_wrapper'       => 'content',

    /*
     * Pagination meta field
     */
    'pagination'                 => [
        //Field name
        'paginate_meta_field' => 'paginate',

        //Fields for exclude from response
        'exclude_fields'      => [
            //'current_page',
            'from',
            //'last_page',
            //'path',
            //'per_page',
            'to',
            //'total',
            'first_page_url',
            'last_page_url',
            'prev_page_url',
            'next_page_url',
            'links',
        ],
    ],

    /*
     * Use meta field.
     */
    'useMeta'                    => true,

    /*
     * Use status field
     */
    'useStatus'                  => true,

    /*
     * Disable Exceptions Details
     */
    'disable_exceptions_details' => false,
];
