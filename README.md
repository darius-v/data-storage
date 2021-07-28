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
    --header 'Cookie: PHPSESSID=27ddba26ea7b3aa77d3a3e39a08284a6' \
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
    curl --location --quest POST 'http://secure-storage.localhost:8000/logout'
  ```