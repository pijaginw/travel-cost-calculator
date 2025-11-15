This Test Plan is designed to cover the critical path of your Symfony application, prioritizing the **Security** (Authentication/Authorization) and **Data Integrity** (Entities) requirements you specified, while adhering to the PRD constraints.

## 1. Testing Environment Setup

Before writing tests, ensure your Symfony environment is ready.
*   **Tool:** PHPUnit (standard with Symfony).
*   **Database:** Use a separate test database (configured in `.env.test`) to avoid wiping your development data.
*   **Base Classes:**
    *   Use `KernelTestCase` for Entity/Unit tests.
    *   Use `WebTestCase` for Controller/Authentication tests.

---

## 2. Phase 1: Unit Tests (Entities & Data Integrity)
**Goal:** Verify that data saved to the database adheres to business rules (Constraints) defined in Section 3 of the PRD.

### 2.1. User Entity
*   **Test Case:** Validate User Email.
    *   *Input:* Invalid email format (e.g., "user.com").
    *   *Expected:* Validation failure.
*   **Test Case:** Unique Email (Constraint).
    *   *Note:* This requires a functional test or a database integration test, but you can test the mapping here.

### 2.2. Trip Entity
*   **Test Case:** Trip Name Constraints (FR-006).
    *   *Input:* Name longer than 70 characters.
    *   *Expected:* Validation error.
    *   *Input:* Empty name.
    *   *Expected:* Validation error.
*   **Test Case:** Currency Selection (FR-005).
    *   *Input:* Null currency.
    *   *Expected:* Validation error (Currency is mandatory).
*   **Test Case:** Trip Association.
    *   *Check:* Ensure a Trip cannot exist without an assigned User.

### 2.3. Expense Entity
*   **Test Case:** Category Validation (FR-014).
    *   *Input:* A category not in the allowed list (Transportation, Accommodation, Food & Drink, Activities, Uncategorized).
    *   *Expected:* Validation error.
*   **Test Case:** Amount Validation.
    *   *Input:* Negative number or zero.
    *   *Expected:* Validation error (Must be positive).

---

## 3. Phase 2: Functional Tests (Authentication & Authorization)
**Goal:** Ensure only the right people can access specific pages (US-001, US-002, US-003) and protect endpoints (FR-001 to FR-004).

### 3.1. Public vs. Protected Pages (Access Control)
*   **Test Case:** Anonymous Access to Public Pages.
    *   *Action:* Request `/login` and `/register`.
    *   *Expected:* HTTP 200 (OK).
*   **Test Case:** Anonymous Access to Protected Pages.
    *   *Action:* Request `/dashboard`, `/trip/new`, or `/trip/{id}`.
    *   *Expected:* HTTP 302 (Redirect to `/login`).

### 3.2. Authentication Flow
*   **Test Case:** Successful Login (US-002).
    *   *Action:* Submit valid credentials via the login form.
    *   *Expected:* Redirect to `/dashboard` (HTTP 302) and Session is authenticated.
*   **Test Case:** Failed Login.
    *   *Action:* Submit invalid credentials.
    *   *Expected:* HTTP 200 (Stay on login page) + display error message "Invalid credentials".
*   **Test Case:** Logout (US-003).
    *   *Action:* Click logout link.
    *   *Expected:* Redirect to login page.

### 3.3. Data Ownership (Critical Security)
*   **Test Case:** User Isolation.
    *   *Scenario:* User A tries to view User B's trip.
    *   *Setup:* Create User A and User B. Create Trip X belonging to User B. Log in as User A.
    *   *Action:* Request `/trip/{id-of-trip-x}`.
    *   *Expected:* HTTP 403 (Forbidden) or 404 (Not Found). **Do not return 200.**

---

## 4. Phase 3: Workflow Integration Tests
**Goal:** Test the actual features working together.

### 4.1. Trip Management
*   **Test Case:** Create a New Trip (US-005).
    *   *Action:* Log in -> Submit "New Trip" form.
    *   *Expected:* Redirect to Dashboard -> Verify new trip appears in the list.
*   **Test Case:** Unique Trip Name per User (FR-006).
    *   *Action:* Create a trip named "Japan 2024". Try to create another trip named "Japan 2024" with the *same* user.
    *   *Expected:* Form error indicating duplicate name.
    *   *Edge Case:* Create "Japan 2024" with *User B*. This *should* be allowed (Names must be unique only for that specific user).

### 4.2. Expense Management (Mocking Required)
*   **Test Case:** Manual Expense Entry (FR-017).
    *   *Action:* Submit the manual expense form.
    *   *Expected:* Expense is saved, and Trip Total Cost is updated.
*   **Test Case:** Delete Expense (FR-018).
    *   *Action:* Click delete on an expense.
    *   *Expected:* Expense removed from database, Total Cost decreases.

> **Note on Testing AI (FR-012):** Do not make real calls to OpenAI in your tests. It is slow and costs money.
> *   **Strategy:** Create a Mock/Stub for your AI Service.
> *   **Test:** Verify that *if* the Service returns "Total: 50, Category: Food", the controller correctly passes that data to the form.

---

## 5. Implementation Guide (Symfony Specifics)

Here is how you should implement the **Authorization** tests, as they are your priority.

### Example: Testing Access Control (The `WebTestCase`)

Create a file `tests/Controller/SecurityControllerTest.php`:

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    // Test that anonymous users are redirected to login
    public function testAnonymousUsersCannotAccessDashboard(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/login');
    }

    // Test that logged-in users can access dashboard
    public function testLoggedInUserCanAccessDashboard(): void
    {
        $client = static::createClient();
        
        // Create a user in your test DB (using a Factory or specific Repository logic)
        // Or utilize a test helper to simulate login
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('test@example.com');

        $client->loginUser($testUser);

        $client->request('GET', '/dashboard');
        $this->assertResponseIsSuccessful();
    }
}
```

### Example: Testing Entity Constraints

Create a file `tests/Entity/TripTest.php`:

```php
<?php

namespace App\Tests\Entity;

use App\Entity\Trip;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TripTest extends KernelTestCase
{
    public function testTripNameCannotBeTooLong(): void
    {
        self::bootKernel();
        $validator = static::getContainer()->get('validator');

        $trip = new Trip();
        // Create a string with 71 characters
        $trip->setName(str_repeat('a', 71)); 
        $trip->setCurrency('USD');

        $errors = $validator->validate($trip);
        
        // Expecting 1 error because max length is 70 (FR-006)
        $this->assertCount(1, $errors);
    }
}
```
