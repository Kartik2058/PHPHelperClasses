# PHP Helper Classes
PHP Helper Classes saves your time and help you to perform CRUD functionality very easily.

# License
You can view License.md file to view the full license. but I am going to give you a quick intro that you are free to use this for any of
your personal and comercial projects but you can't sell these PHP Classes without my permission.

# How to add these classes to your project
It's very simple to add these classes to your project. Just download this project and then create a Classes folder in your project and then
copy all these classes to your that Classes folder and you are all done. Next you just need to use include function to incluse Database.php
file in your project. Next you need to change the Database credential info in Database.php just fill your Host, Username, Password, Database
Name. Next is to create your own custom class and make sure that your class extend Database.php class and also make sure that your custom
class name is in singular form of our table name. You can also see User.php class to know how to set it up.

# Documentation
Here is a quick documentation of every function and file present in this proeject.

## Database Class
The  Database class Help you to perform CRUD action on your table.

### Constructor Method
Constructor method first set up a connection to the database using PDO. Next it create a property names table_name which will store the
name of table it will automatically get the name of the table by your class name so for example if your class name is User it will lowercase it
and then make it plural so in this case it will be users so the name of your table will be users. Next it will fetch all the Field/Columns present in
the table using getColumnNames method and make an array and store it in a propery called column_names.

### Find All Method
find_all method will fetch all the records present in the table and create an array and in that array will store the object of your class
which will have the properties. for example 
```
$users = User::find_all();
foreach($users as $user) {
  echo $user->name . "<br>";
}
```
So in this case there is class named User class which extend from Database class which is present in the project so what this code does is
first it fetch all the records from the table named users as the class name is User. and then it makes an array and in that array store
the object of the class that's why we are using `$user->name` so we loop through that array and echo the names property which we got from the
database.

### Find By Id Method
find _by_id method takes two parameter the first parameter is an integer so it's like 12. The first parameter is the actual id which
will be used to get the record from the database. The second parameter is optional it's basically the name of the column in most cases
it will be `id` but if you will not provide it. then it will search the database and use the primary key as your id column let's see an example.
```
$user = User::find_by_id(1);
echo $user->name;
```
In this example $user variable store the instance of User object. And this object also store the data of the user record of id `1` present in table.
this example was just to use only one parameter let's see another example.
```
$user = User::find_by_id(1, "id");
echo $user->name;
```
This example is basically the same as the another one but this one asumes `id` column as the id column in the table.

### Find By Where Method
find_by_where method takes a lot of parameter to generate a query. let's go over it.
so the first two parameter is where statement and where params. The where statement is just a statement like `name = ? AND email = ?` and where params
is an array like this ["Your Name", "youremail@example.com"]
So, this query will select all record which will have name = "Your Name" and the email = "youremail@example.com"
Next there is another param which is columns this will be an array so for example ["name","email"]
what this will do is it will select only name and email from the table
next there are another two params which is order type and order column
the order type will be will be either DESC or ASC
the order column will be like "name,email" this columns according to the which the query will be ordered.
Next we have limit param which will an integer which is the limit of the params.
let's see an example
```1
$users = User::find_by_where("name = ? AND email = ?", ["Your Name, "Your Email"], ["column_1","column_2"], "ASC", "id", 5);
foreach ($users as $user) {
  echo $user->name . "<br>";
}
```
this will create the following query.
`SELECT column_1, column_2 FROM users WHERE name = ? AND email = ? ORDER BY id ASC LIMIT 5`
note that this is PDO query so the params of this query is ["Your Name", "Your Email"].

### Create Method
create method will just insert data into the table let's look at an example.
```
$data = ["name"=>"Kartik Arora", "email"=>"whatever@email.youhave", "password"=>"keepitsecret"];
$user = User::create($data);
echo $user->id . "<br>";
echo $user->name . "<br>";
```
this will first create an associative array called data and have the column name as key and value as data.
then it will query the database by passing the data array into the create method.
the create method will return an object.
you can use this object to print any value like the id, name or anything.

### Update Method
update method will update a record present in a table let's look at an example``
```
$user = User::find_by_id(1);
$data = ["name"=>"updated name"];
$user->update($data);
echo $user->name; // the updated name will be printed
```
This will first find a user using find_by_id method and then create an array called name which will have only those field which you want
to update. then it will run update method on the $user object passing in the data object. and update the database.

### Delete Method
As the name suggest the delete method just delete a record from the table.
let's look at an example.
```
$user = User::find_by_id(1);
$user->delete();
```
$first it will find a user using find_by_id function and then run delete method on $user object and delete the record from the table.
