define({ "api": [
  {
    "type": "post",
    "url": "/v1/categories/favorite",
    "title": "favoriteCategories",
    "version": "0.1.0",
    "name": "favoriteCategories",
    "group": "Categories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>array</p> ",
            "optional": false,
            "field": "category_ids",
            "description": "<p>example in json=['1','2']</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CategoriesController.php",
    "groupTitle": "Categories",
    "sampleRequest": [
      {
        "url": "/api/v1/categories/favorite"
      }
    ]
  },
  {
    "type": "get",
    "url": "/v1/categories",
    "title": "getCategories",
    "version": "0.1.0",
    "name": "getCategories",
    "group": "Categories",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "id",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CategoriesController.php",
    "groupTitle": "Categories",
    "sampleRequest": [
      {
        "url": "/api/v1/categories"
      }
    ]
  },
  {
    "type": "get",
    "url": "/v1/categories/favorites",
    "title": "getFavoriteCategories",
    "version": "0.1.0",
    "name": "getFavoriteCategories",
    "group": "Categories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CategoriesController.php",
    "groupTitle": "Categories",
    "sampleRequest": [
      {
        "url": "/api/v1/categories/favorites"
      }
    ]
  },
  {
    "type": "get",
    "url": "/v1/categories/unfavorite",
    "title": "unfavoriteCategories",
    "version": "0.1.0",
    "name": "unfavoriteCategories",
    "group": "Categories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>array</p> ",
            "optional": false,
            "field": "category_ids",
            "description": "<p>example=['1','2']</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CategoriesController.php",
    "groupTitle": "Categories",
    "sampleRequest": [
      {
        "url": "/api/v1/categories/unfavorite"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users/events/follow",
    "title": "followEvents",
    "version": "0.1.0",
    "name": "followEvents",
    "group": "Events",
    "description": "<p>Иду на событие</p> ",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>integer</p> ",
            "optional": false,
            "field": "event_id",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/users/events/follow"
      }
    ]
  },
  {
    "type": "get",
    "url": "/v1/events/:id",
    "title": "getEvents",
    "version": "0.1.0",
    "name": "getEvents",
    "group": "Events",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "id",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/events/:id"
      }
    ]
  },
  {
    "type": "get",
    "url": "/v1/users/events/favorite",
    "title": "getEventsByFavoriteCategories",
    "version": "0.1.0",
    "name": "getEventsByFavoriteCategories",
    "group": "Events",
    "description": "<p>Все события из категорий на которые подписан пользователь</p> ",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/users/events/favorite"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/events",
    "title": "storeEvents",
    "version": "0.1.0",
    "name": "storeEvents",
    "group": "Events",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "category_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "title",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "date",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "time",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "allowedValues": [
              "'private'",
              "'public'"
            ],
            "optional": false,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "price",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>array</p> ",
            "optional": false,
            "field": "images",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "place_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "address",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>datetime</p> ",
            "optional": false,
            "field": "date_stop",
            "description": "<p>Дата окончания показа в приложении</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/events"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/events/unfollow",
    "title": "unfollowEvents",
    "version": "0.1.0",
    "name": "unfollowEvents",
    "group": "Events",
    "description": "<p>НЕ иду на событие</p> ",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>integer</p> ",
            "optional": false,
            "field": "event_id",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/events/unfollow"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/events/:id",
    "title": "updateEvents",
    "version": "0.1.0",
    "name": "updateEvents",
    "group": "Events",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>integer</p> ",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "category_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "title",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "date",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "time",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "price",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "place_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "address",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>datetime</p> ",
            "optional": false,
            "field": "date_stop",
            "description": "<p>Дата окончания показа в приложении</p> "
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/EventsController.php",
    "groupTitle": "Events",
    "sampleRequest": [
      {
        "url": "/api/v1/events/:id"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users/auth/email",
    "title": "AuthByEmail",
    "version": "0.1.0",
    "name": "AuthByEmail",
    "group": "Users",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "email",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "password",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "/api/v1/users/auth/email"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users/auth/hidden",
    "title": "AuthByHidden",
    "version": "0.1.0",
    "name": "AuthByHidden",
    "group": "Users",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "imei",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "/api/v1/users/auth/hidden"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users/auth/:type",
    "title": "AuthBySocial",
    "version": "0.1.0",
    "name": "AuthBySocial",
    "group": "Users",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "allowedValues": [
              "'vk'",
              "'fb'",
              "'hidden'"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>Hidden=если пользователь ни как не авторизировался для учёта инфы</p> "
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "social_hash",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "/api/v1/users/auth/:type"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users",
    "title": "regUser",
    "version": "0.1.0",
    "name": "regUser",
    "group": "Users",
    "description": "<p>Только для авторизации по имейлу нужна регистрация,в остальных случаях она автоматическая</p> ",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "email",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "password",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "/api/v1/users"
      }
    ]
  },
  {
    "type": "post",
    "url": "/v1/users/:id",
    "title": "updateUser",
    "version": "0.1.0",
    "name": "updateUser",
    "group": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>User token</p> "
          }
        ]
      }
    },
    "description": "<p>При редактировании, если нужно какое-то определённое поле, в других должно быть false (bool)</p> ",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>file</p> ",
            "optional": false,
            "field": "image",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "email",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "password",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "phone_1",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "phone_2",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": true,
            "field": "phone_3",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "location",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "lon",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "lat",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "category_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "place_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "allowedValues": [
              "\"ios\"",
              "\"android\""
            ],
            "optional": false,
            "field": "device_type",
            "description": "<p>для пушей</p> "
          },
          {
            "group": "Parameter",
            "type": "<p>string</p> ",
            "optional": false,
            "field": "device_token",
            "description": ""
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UsersController.php",
    "groupTitle": "Users",
    "sampleRequest": [
      {
        "url": "/api/v1/users/:id"
      }
    ]
  }
] });