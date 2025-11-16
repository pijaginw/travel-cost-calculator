# Travel Cost Calculator

A web application designed to simplify expense tracking for solo leisure travelers. The product's core function is to calculate the total cost of a trip by allowing users to upload images of their receipts. It leverages a third-party AI service to automatically extract key information, minimizing manual data entry and helping users answer the question, "How much have I spent on this trip so far?" with minimal effort.

-----

## Table of Contents

  * [Tech Stack](https://www.google.com/search?q=%23tech-stack)
  * [Getting Started Locally](https://www.google.com/search?q=%23getting-started-locally)
  * [Available Scripts](https://www.google.com/search?q=%23available-scripts)
  * [Project Scope](https://www.google.com/search?q=%23project-scope)
  * [Project Status](https://www.google.com/search?q=%23project-status)
  * [License](https://www.google.com/search?q=%23license)

-----

## Tech Stack

The application is built with a modern PHP stack, focusing on simplicity and rapid development for the MVP.

| Category | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | **PHP 8.1+** | The core server-side programming language. |
| | **Symfony** | A high-performance PHP framework used for routing, security, and database interaction (via Doctrine ORM). |
| | **Composer** | The dependency manager for all PHP packages. |
| **Frontend** | **Symfony Forms (Twig)** | Server-rendered forms for a simple UI tightly integrated with the backend. |
| | **CSS** | Standard styling for a clean and user-friendly interface. |
| **Database** | **PostgreSQL** | A powerful, open-source relational database for all persistent data. |
| **CI/CD** | **GitHub Actions** | Automation pipeline for testing, building, and deploying the application. |

-----

## Getting Started Locally

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

You will need the following software installed on your system:

  * PHP `^8.1`
  * [Composer](https://getcomposer.org/)
  * [Symfony CLI](https://symfony.com/download)
  * [PostgreSQL](https://www.postgresql.org/download/)

### Installation

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/travel-cost-calculator.git
    cd travel-cost-calculator
    ```

2.  **Install PHP dependencies:**

    ```bash
    composer install
    ```

3.  **Configure environment variables:**
    Create a local environment file by copying the example. You will need to fill in your database credentials and your OpenAI API key.

    ```bash
    cp .env .env.local
    ```

    Open `.env.local` and configure the following variables:

    ```env
    # .env.local
    DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=15&charset=utf8"
    OPENAI_API_KEY="your-openai-api-key-here"
    ```

4.  **Set up the database:**
    Run the following Symfony commands to create the database and apply the necessary migrations.

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

5.  **Run the local server:**
    Use the Symfony CLI to start the application.

    ```bash
    symfony server:start
    ```

    The application will be available at `https://127.0.0.1:8000`.

## Getting Started Locally with Docker 🐳

To run the application using **Docker**, which bypasses the need to install **PHP**, **Composer**, and **PostgreSQL** locally, follow these alternative steps. This method uses the provided `docker-compose.yml` file to set up all necessary services (PHP-FPM, Nginx, and PostgreSQL).

### Prerequisites (Docker Method)

You will need the following software installed on your system:

* **[Docker](https://www.docker.com/products/docker-desktop)**
* **[Docker Compose](https://docs.docker.com/compose/install/)** (often included with Docker Desktop)

### Installation (Docker Method)

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/travel-cost-calculator.git
    cd travel-cost-calculator
    ```

2.  **Configure environment variables:**
    Create a local environment file by copying the example. You will need to fill in your **OpenAI API key**.

    ```bash
    cp .env .env.local
    ```

    Open `.env.local` and configure the following variable:

    ```env
    # .env.local
    OPENAI_API_KEY="your-openai-api-key-here"
    ```

    > **Note:** The `docker-compose.yml` file automatically uses `app` for the database name, user, and password, which must be consistent with the `DATABASE_URL` setting in your `.env` and `.env.local` files.

3.  **Build and Run the containers:**
    The first time you run this, it will build the custom `php` service image and download the `nginx` and `postgres` images.

    ```bash
    docker compose up --build -d
    ```

4.  **Set up the database:**
    Once the containers are running, you must execute the database setup commands *inside* the running **PHP container** (`symfony_php`).

    ```bash
    # Run the database creation command inside the PHP container
    docker exec symfony_php php bin/console doctrine:database:create

    # Run the migrations command inside the PHP container
    docker exec symfony_php php bin/console doctrine:migrations:migrate --no-interaction
    ```

5.  **Access the application:**
    The Nginx service is configured to expose the application on port **8080** of your host machine.

    The application will be available at `http://localhost:8080`.

### Stopping Docker

To stop and remove the containers, use the following command:

```bash
docker compose down
```

-----

## Project Scope

The scope for the Minimum Viable Product (MVP) is tightly focused on solving the core user problem.

### ✅ In Scope for MVP

  * User registration and authentication (login/logout).
  * Creation and management of trips with a single, fixed currency per trip.
  * Single file upload for expenses (**JPG** and **PNG** formats only).
  * AI-powered data extraction for amount and category using OpenAI.
  * Manual correction and entry of expense data.
  * Dashboard view of all trips and a detailed summary view for each trip.
  * A simple web application interface.

### ❌ Out of Scope for MVP

  * Batch/multi-file expense uploading.
  * Support for other file formats (e.g., PDF, CSV, XLSX).
  * Multi-currency conversion within a single trip.
  * Expense splitting, group travel features, or collaborative trip management.
  * Budgeting features (e.g., setting a budget and tracking against it).
  * Native mobile applications (iOS/Android).
  * Paid subscriptions or premium features.

-----

## Project Status

**Current Phase:** In Development 🚧

This project is currently being developed as a **Minimum Viable Product (MVP)**. The primary focus is on implementing the core features defined in the project scope.

-----

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
