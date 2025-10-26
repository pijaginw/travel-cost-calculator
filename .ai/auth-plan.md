# Authentication Module: Technical Specification
This document outlines the architecture for the user authentication, registration, and password recovery functionalities for the Travel Cost Calculator, built on the specified PHP/Symfony stack.

## 1. 🏛️ User Interface Architecture (Twig & Symfony Forms)
The frontend will be rendered server-side using **Twig** templates and **Symfony Forms**, ensuring tight integration with the backend logic.

### 1.1. Layouts
* **`layout/base.html.twig`**: The main application layout.
    * **Authenticated State**: Will include the main navigation bar containing links to "My Trips" (Dashboard), "Create New Trip," and a "Logout" button. It will also display the user's email.
    * **Unauthenticated State**: This layout will *not* be used for unauthenticated users, as they will be restricted to the auth pages.
* **`layout/auth.html.twig`**: A minimal layout for public-facing forms.
    * **Functionality**: Provides a simple, centered container for the login, registration, and password recovery forms. It will not contain the main application navigation.

### 1.2. Pages (Twig Views) & Forms
* **Login Page (`templates/security/login.html.twig`)**
    * **Route**: `GET /login`
    * **Functionality**: Displays the login form with "Email" and "Password" fields. This form will be handled by the Symfony Security component.
    * **Components**: Includes a link to the "Forgot Password?" page (`/forgot-password`) and the "Sign Up" page (`/register`).
* **Registration Page (`templates/registration/register.html.twig`)**
    * **Route**: `GET /register`
    * **Form**: Uses a Symfony Form (`RegistrationFormType`) to render the form.
    * **Fields**: Email, Password (with confirmation), and a checkbox to agree to terms (if required).
* **Forgot Password Request Page (`templates/reset_password/request.html.twig`)**
    * **Route**: `GET /forgot-password`
    * **Form**: A simple form with one "Email" field.
    * **Functionality**: On submission, it will send a password reset link to the user's email (if the account exists).
* **Reset Password Page (`templates/reset_password/reset.html.twig`)**
    * **Route**: `GET /reset-password/{token}`
    * **Form**: A form with "New Password" and "Confirm New Password" fields.
    * **Functionality**: The page is accessed via the unique, time-sensitive token sent to the user's email.

### 1.3. UI Validation & Error Handling
Validation will be handled by **Symfony Forms** using **Symfony Validator** constraints. Errors will be rendered inline next to the form fields.

* **Registration (`US-001`)**:
    * "Email is required."
    * "This email is already in use." (`UniqueEntity` constraint)
    * "Please enter a valid email address."
    * "Password must be at least 8 characters long."
    * "The passwords do not match."
* **Login (`US-002`)**:
    * A single, generic error message will be displayed: "Invalid credentials."
* **Password Reset**:
    * "If an account with this email exists, a reset link has been sent." (On the request page, to prevent user enumeration).
    * "The reset token is invalid or has expired." (On the reset page).

### 1.4. Key Scenarios
* **Anonymous User Access**:
    1.  User visits `/` (Dashboard).
    2.  Symfony's firewall intercepts the request.
    3.  User is redirected to `/login`.
* **Successful Login (`US-002`)**:
    1.  User submits valid credentials on `/login`.
    2.  Symfony Security authenticates the user and creates a session.
    3.  User is redirected to their intended destination (or the dashboard `/` as a default).
* **Successful Registration (`US-001`)**:
    1.  User submits a valid registration form on `/register`.
    2.  The backend creates the user account.
    3.  The system *automatically logs the user in*.
    4.  User is redirected to the dashboard (`/`), where they will see the "Create Your First Trip" prompt (as per `US-004`).
* **Accessing Protected Routes (`US-007`)**:
    1.  A logged-in user clicks "Add Expense" from a Trip Summary page.
    2.  The firewall confirms the user is `IS_AUTHENTICATED_FULLY`.
    3.  The user is shown the "Add Expense" form.

---

## 2. ⚙️ Backend Logic
The backend logic will be managed by Symfony Controllers, Services, and Doctrine Entities.

### 2.1. Data Models (Doctrine Entities)
* **`App\Entity\User`**
    * **Purpose**: Stores user account information.
    * **Interfaces**: Must implement `UserInterface` and `PasswordAuthenticatedUserInterface`.
    * **Fields**:
        * `id` (int, PK)
        * `email` (string, 255, unique)
        * `password` (string, 255) - Will store the hashed password.
        * `roles` (json) - Stores user roles (e.g., `["ROLE_USER"]`).
    * **Relations**:
        * `OneToMany` with `Trip` entity (a user can have many trips).

### 2.2. Controller Routes & Actions
* **`App\Controller\SecurityController`**
    * `GET /login`: Renders the login form.
    * `POST /login`: Handled entirely by the security system. This action may be empty.
    * `GET /logout`: Handled entirely by the security system. This action may be empty.
* **`App\Controller\RegistrationController`**
    * `GET|POST /register`:
        * Renders the `RegistrationFormType`.
        * On `POST`, it validates the form, hashes the password using `UserPasswordHasherInterface`, persists the new `User` entity, and programmatically logs the user in using `Security::login()`.
* **`App\Controller\ResetPasswordController`** (Leveraging `symfonycasts/reset-password-bundle`)
    * `GET|POST /forgot-password`: Renders the request form. On `POST`, it generates a reset token and sends the email via `symfony/mailer`.
    * `GET|POST /reset-password/{token}`: Validates the token. On `POST`, it validates the new password, hashes and updates the user's password, and logs them in.
* **Protected Controllers (e.g., `TripController`, `ExpenseController`)**
    * These controllers (handling `US-004`, `US-007`, etc.) will not require specific auth logic *within* them. They will be automatically protected by the firewall.
    * They can access the current user by type-hinting the `UserInterface` or calling `$this->getUser()`.

### 2.3. Validation
* Validation will be implemented using **Symfony Validator constraints** on the `User` entity and the `RegistrationFormType` DTO.
* **Key Constraints**: `NotBlank()`, `Email()`, `Length(min=8)`, `UniqueEntity(fields={"email"})`.

### 2.4. Exception Handling
* **`AuthenticationException`**: Thrown on invalid login. The security system catches this and re-renders the login form with the error message.
* **`AccessDeniedException`**: Thrown if a user tries to access a resource they don't own (e.g., another user's trip). This will be caught and can be configured to show a 403 error.
* **`ResetPasswordException`**: Thrown by the reset password bundle for invalid/expired tokens, resulting in a redirect back to the request page with an error.

---

## 3. 🔒 Authentication System (Symfony Security)
The core of the system will be configured in `config/packages/security.yaml`.

### 3.1. Password Hashing
* **Component**: `password_hashers`
* **Configuration**: The `App\Entity\User` entity will be configured to use `auto` (defaulting to `bcrypt` or `argon2id`), which is managed by Symfony.

### 3.2. User Provider
* **Component**: `providers`
* **Configuration**: An `entity` provider will be configured to load users from the `App\Entity\User` entity using the `email` property as the identifier.

### 3.3. Firewall
* **Component**: `firewalls`
* **`dev` firewall**: Configured for development/debug routes.
* **`main` firewall**: This is the primary application firewall.
    * **`pattern`**: `^/` (covers all routes).
    * **`lazy`**: `true`
    * **`provider`**: Set to the Doctrine entity provider defined above.
    * **`form_login`**:
        * `login_path`: `app_login` (route name for `GET /login`).
        * `check_path`: `app_login` (route name for `POST /login`).
        * `enable_csrf`: `true`.
        * `default_target_path`: `app_dashboard` (route name for the "My Trips" dashboard).
    * **`logout`**:
        * `path`: `app_logout` (route name for `GET /logout`).
        * `target`: `app_login` (redirect to login page after logout).
    * **`remember_me`**: Can be enabled to allow users to stay logged in.

### 3.4. Access Control
This is critical for securing the application (`US-007`, etc.).
* **Configuration**: `access_control`
* **Rules**:
    * `{ path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }`
    * `{ path: ^/register, roles: IS_AUTHENTICATED_ANONYMOUSLY }`
    * `{ path: ^/forgot-password, roles: IS_AUTHENTICATED_ANONYMOUSLY }`
    * `{ path: ^/reset-password, roles: IS_AUTHENTICATED_ANONYMOUSLY }`
    * `{ path: ^/, roles: IS_AUTHENTICATED_FULLY }` (This single rule protects all other routes, including the dashboard, trip creation, and expense uploading).

### 3.5. Key Services & Contracts
* **`Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface`**: Injected into `RegistrationController` and `ResetPasswordController` to securely hash new passwords.
* **`Doctrine\ORM\EntityManagerInterface`**: Injected to persist the new `User` entity.
* **`Symfony\Bundle\SecurityBundle\Security`**: Injected into `RegistrationController` to programmatically log the user in after registration (`US-001`).
* **`SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface`**: (From `symfonycasts/reset-password-bundle`) Injected to handle token generation and validation for password recovery.
* **`Symfony\Component\Mailer\MailerInterface`**: Injected to send the password reset email.
