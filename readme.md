#Tokens
You use following 2 token that can be used
hello@123
fresh@123 

#API
Following are the list of API that are available

##CRUD Operations
[GET] recipes/index.php

[GET] recipes/index.php/<recipe_id>

[POST] recipes/index.php?token=<token>
{
	"name": "recipe name",
	"time": 204,
	"difficulty": 2,
	"vegetarian": false
} 

[PUT/PATCH] recipes/index.php/<recipe_id>?token=<token>
{
	"name": "recipe updated",
	"time": 204,
	"difficulty": 2,
	"vegetarian": false
} 

[DELETE] recipes/index.php/<recipe_id>?token=<token>

##Search Operations
[GET] recipes/index.php/search

[GET] recipes/index.php/search/<keyword>

##Rate Operation
[POST] recipes/index.php/rate/<recipe_id>?token=<token>
{
	"name":"name",
	"rate" : 1
}