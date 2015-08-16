NodeJS Array Adapter
--------------------

Test data source for our table:

```javascript
var data = [];

for (var i = 1; i <= 100; i++) {
    data.push({
        id: i,
        name: "User " + i,
        email: "user" + i + "@example.com",
        created_at: new Date(2000 + i, 1, 1, 10, 15, 25),
        is_admin: false,
    });
}
```

Create adapter:

```javascript
var ArrayAdapter = require('dynamic-table').arrayAdapter();

// ...

var adapter = new ArrayAdapter();
adapter.setData(data);
```

Connect adapter to the table:

```javascript
table.setAdapter(adapter);
```

... continue with the table
