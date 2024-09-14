
# Laravel Setup Guide

## Prerequisites
#### Before you begin, ensure you have the following installed:

##### PHP (version 8.1 or higher)
##### Composer
##### MySQL
##### Laravel CLI

## Setup Project
###### 1. git clone https://github.com/adimgozali13/blog-api.git cd blog-api
###### 2. Install Dependencies
#### composer install
###### 3. Setup Environmen
#### cp .env.example .env
###### Edit the .env file with your credentials.
###### 4. Generate Application Key
#### php artisan key:generate
###### 5. Migrate Database
#### php artisan migrate
###### 6. Setup Queue Laravel uses queues to process jobs asynchronously. Ensure you have configured the queue in the .env file: 
#### QUEUE_CONNECTION=database
###### Create the queue tables by running the following commands:
#### "php artisan queue:table"
#### "php artisan migrate"
###### To run the queue worker, use the following command:
#### php artisan queue:work
##### 7. Start Laravel Development Server
#### php artisan serve
## Testing
#### php artisan test

