# Secure Information Storage REST API

### Project setup

* Add `secure-storage.localhost` to your `/etc/hosts`: `127.0.0.1 secure-storage.localhost`

* Run `make init` to initialize project

* Open in browser: http://secure-storage.localhost:8000/item Should get `Full authentication is required to access this resource.` error, because first you need to make `login` call (see `postman_collection.json` or `SecurityController` for more info).

### Run tests

make tests

### API credentials

* User: john
* Password: maxsecure

### Postman requests collection

You can import all available API calls to Postman using `postman_collection.json` file

# Api documentation

If you are trying to call endpoint which requires to be logged in, you will get 403 error:

`{"error":"Unauthorized, you need to log in."}`

**Login**
----
Logs user in.

* **URL**

  /login

* **Method:**

  `POST`

* **Data Params**

  `{
  "username": "john",
  "password": "maxsecure"
  }`

* **Success Response:**

    * **Code:** 200 <br />
      **Content:** `{
      "username": "john",
      "roles": [
      "ROLE_USER"
      ]
      }`

* **Error Response:**

    * **Code:** 401 UNAUTHORIZED <br />
      **Content:** `{
      "error": "Invalid credentials."
      }`

* **Sample Call:**

  ```
    curl --location --request POST 'http://secure-storage.localhost:8000/login' \
    --header 'Content-Type: application/json' \
    --data-raw '{
    "username": "john",
    "password": "maxsecure"
    }'
  ```

**Logout**
----
Logs user out.

* **URL**

  /logout

* **Method:**

  `POST`

* **Success Response:**

    * **Code:** 200 <br />
      **Content:** `[]`
      
* **Sample Call:**

  ```
    curl --location --request POST 'http://secure-storage.localhost:8000/logout'
  ```

**Get user items list**
----
Returns list of logged in user items.

* **URL**

  /item

* **Method:**

  `GET``

* **Success Response:**

    * **Code:** 200 <br />
      **Content:** `[{"id":"13","data":"dar","created_at":{"date":"2021-07-22 08:02:41.000000","timezone_type":3,"timezone":"UTC"},"updated_at":{"date":"2021-07-27 07:17:58.000000","timezone_type":3,"timezone":"UTC"}}]`

* **Sample Call:**

  ```
  curl --location --request GET 'http://secure-storage.localhost:8000/item' \
    --header 'Cookie: PHPSESSID=fe9ceb2619e0a4df023b2fb24c49e126'
  ```
  
