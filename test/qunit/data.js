var $description = {
    "columns": {
        "id": {
            "title": "ID",
            "type": "integer",
            "filters": [],
            "sortable": false,
            "visible": false
        },
        "string": {
            "title": "String",
            "type": "string",
            "filters": [ "like", "null" ],
            "sortable": true,
            "visible": true
        },
        "integer": {
            "title": "Integer",
            "type": "integer",
            "filters": [ "between", "null" ],
            "sortable": true,
            "visible": true
        },
        "float": {
            "title": "Float",
            "type": "float",
            "filters": [ "between", "null" ],
            "sortable": true,
            "visible": true
        },
        "boolean": {
            "title": "Boolean",
            "type": "boolean",
            "filters": [ "equal", "null" ],
            "sortable": true,
            "visible": true
        },
        "datetime": {
            "title": "DateTime",
            "type": "datetime",
            "filters": [ "between", "null" ],
            "sortable": true,
            "visible": true
        }
    },
    "success": true
};

var $dataFirstPage = {
    "sort_column": "string",
    "sort_dir": "asc",
    "page_number": 1,
    "page_size": 5,
    "total_pages": 3,
    "filters": {},
    "rows": [
        {
            "id": 1,
            "string": "string 1",
            "integer": 1,
            "float": 0.01,
            "boolean": false,
            "datetime": 1273572010
        },
        {
            "id": 2,
            "string": "string 2",
            "integer": 2,
            "float": 0.02,
            "boolean": true,
            "datetime": 1273572020
        },
        {
            "id": 3,
            "string": null,
            "integer": null,
            "float": null,
            "boolean": null,
            "datetime": null
        },
        {
            "id": 4,
            "string": "string 4",
            "integer": 4,
            "float": 0.04,
            "boolean": true,
            "datetime": 1273572040
        },
        {
            "id": 5,
            "string": "string 5",
            "integer": 5,
            "float": 0.05,
            "boolean": false,
            "datetime": 1273572050
        }
    ],
    "success": true
};

var $dataMiddlePage = {
    "sort_column": "string",
    "sort_dir": "asc",
    "page_number": 2,
    "page_size": 5,
    "total_pages": 3,
    "filters": {},
    "rows": [
        {
            "id": 1,
            "string": "string 1",
            "integer": 1,
            "float": 0.01,
            "boolean": false,
            "datetime": 1273572010
        },
        {
            "id": 2,
            "string": "string 2",
            "integer": 2,
            "float": 0.02,
            "boolean": true,
            "datetime": 1273572020
        },
        {
            "id": 3,
            "string": null,
            "integer": null,
            "float": null,
            "boolean": null,
            "datetime": null
        },
        {
            "id": 4,
            "string": "string 4",
            "integer": 4,
            "float": 0.04,
            "boolean": true,
            "datetime": 1273572040
        },
        {
            "id": 5,
            "string": "string 5",
            "integer": 5,
            "float": 0.05,
            "boolean": false,
            "datetime": 1273572050
        }
    ],
    "success": true
};

var $dataLastPage = {
    "sort_column": "string",
    "sort_dir": "asc",
    "page_number": 3,
    "page_size": 5,
    "total_pages": 3,
    "filters": {},
    "rows": [
        {
            "id": 1,
            "string": "string 1",
            "integer": 1,
            "float": 0.01,
            "boolean": false,
            "datetime": 1273572010
        },
        {
            "id": 2,
            "string": "string 2",
            "integer": 2,
            "float": 0.02,
            "boolean": true,
            "datetime": 1273572020
        },
        {
            "id": 3,
            "string": null,
            "integer": null,
            "float": null,
            "boolean": null,
            "datetime": null
        },
        {
            "id": 4,
            "string": "string 4",
            "integer": 4,
            "float": 0.04,
            "boolean": true,
            "datetime": 1273572040
        },
        {
            "id": 5,
            "string": "string 5",
            "integer": 5,
            "float": 0.05,
            "boolean": false,
            "datetime": 1273572050
        }
    ],
    "success": true
};

var $showData = 'middle';
