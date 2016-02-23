NodeJS Array Adapter
--------------------

Test data source for our table:

```javascript
var data = [];
var dt = moment("2010-05-11 13:00:00");
for (var i = 1; i <= 100; i++) {
    dt.add(10, 'seconds');
    if (i == 3) {
        data.push({
            id: i,
            name: null,
            email: null,
            created_at: null,
            is_admin: null,
        });
    } else {
        data.push({
            id: i,
            name: "User " + i,
            email: "user" + i + "@example.com",
            created_at: dt.format("YYYY-MM-DD HH:mm:ss"),           // as a string
            // created_at: dt.unix(),                               // or as a timestamp
            // created_at: clone(dt.toDate()),                      // or as JS Date object
            // created_at: clone(dt),                               // or as a moment.js object
            is_admin: (i % 2 == 0),
        });
    }
}
```

Create adapter:

```javascript
var ArrayAdapter = require('dynamic-table').arrayAdapter();

// ...

var adapter = new ArrayAdapter();
adapter.setData(data);              // <-- Feed our array to the adapter
// adapter.setDbTimezone('UTC');    // Data source could be in different timezone
```

Connect adapter to the table:

```javascript
table.setAdapter(adapter);
```

... continue with the table
