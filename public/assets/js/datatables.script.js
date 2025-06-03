/**
 * DataTables Mobile Configuration
 * Provides mobile-responsive DataTable initialization
 */
window.DataTablesMobileConfig = {
    initTable: function(selector, type, options) {
        // Default configuration for mobile responsiveness
        var defaultConfig = {
            responsive: true,
            processing: true,
            select: {
                style: 'multi',
                selector: '.select-checkbox',
                items: 'row',
            },
            dom: "<'row'<'col-sm-12 col-md-7'lB><'col-sm-12 col-md-5 p-0'f>>rtip",
            oLanguage: {
                sLengthMenu: "_MENU_",
                sSearch: '',
                sSearchPlaceholder: "Search..."
            },
            buttons: [
                {
                    extend: 'collection',
                    text: 'EXPORT',
                    buttons: [
                        {
                            extend: 'csv',
                            text: 'CSV',
                            exportOptions: {
                                columns: ':not(.no-export):not(:last-child)', // Exclude action columns
                                format: {
                                    body: function (data, row, column, node) {
                                        // Remove HTML tags and get clean text
                                        var $node = $(node);
                                        var input = $node.find('input');
                                        if (input.length) {
                                            return input.val();
                                        }
                                        return $node.text().trim();
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                columns: ':not(.no-export):not(:last-child)',
                                format: {
                                    body: function (data, row, column, node) {
                                        var $node = $(node);
                                        var input = $node.find('input');
                                        if (input.length) {
                                            return input.val();
                                        }
                                        return $node.text().trim();
                                    }
                                }
                            }
                        },
                        {
                            extend: 'pdf',
                            text: 'PDF',
                            exportOptions: {
                                columns: ':not(.no-export):not(:last-child)',
                                format: {
                                    body: function (data, row, column, node) {
                                        var $node = $(node);
                                        var input = $node.find('input');
                                        if (input.length) {
                                            return input.val();
                                        }
                                        return $node.text().trim();
                                    }
                                }
                            }
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            exportOptions: {
                                columns: ':not(.no-export):not(:last-child)',
                                format: {
                                    body: function (data, row, column, node) {
                                        var $node = $(node);
                                        var input = $node.find('input');
                                        if (input.length) {
                                            return input.val();
                                        }
                                        return $node.text().trim();
                                    }
                                }
                            }
                        }
                    ]
                }
            ]
        };

        // Merge user options with default config
        var config = $.extend(true, {}, defaultConfig, options);

        // Initialize and return the DataTable instance
        return $(selector).DataTable(config);
    }
};