```markdown
# Product Requirements Document (PRD) - Travel Cost Calculator
## 1. Product Overview
The Travel Cost Calculator is a web application designed to simplify expense tracking for solo leisure travelers. The product's core function is to calculate the total cost of a trip by allowing users to upload images of their receipts (JPG, PNG). It leverages a third-party AI service (OpenAI) to automatically extract key information like the total amount and expense category, minimizing manual data entry. The Minimum Viable Product (MVP) prioritizes speed, simplicity, and accuracy, providing a streamlined experience for creating trips, adding expenses, and viewing total costs. Users are required to create an account to save and manage their trip history.

## 2. User Problem
Solo leisure travelers often struggle with tracking their expenses accurately and efficiently. Traditional methods like keeping physical receipts, manually entering data into spreadsheets, or using complex, business-oriented expense tracking apps are cumbersome and time-consuming. Travelers need a straightforward tool that allows them to quickly capture an expense at the moment it occurs and see an aggregated total for their trip without a steep learning curve or unnecessary features. The primary goal is to answer the simple question, "How much have I spent on this trip so far?" with minimal effort.

## 3. Functional Requirements
### 3.1. User Account Management
- FR-001: Users must be able to sign up for a new account using an email address and password.
- FR-002: Users must be able to log in to their existing account.
- FR-003: Users must be able to log out of their account.

### 3.2. Trip Management
- FR-004: Logged-in users must be able to create a new trip.
- FR-005: A new trip requires a trip name and a single trip currency.
- FR-006: The trip name must be unique for that user and have a maximum length of 70 characters.
- FR-007: Users must have a dashboard view ("My Trips") that displays a summary card for each trip they have created.
- FR-008: Each trip card on the dashboard must display the Trip Name, the running Total Cost, and the number of individual expenses.
- FR-009: Users must be able to click a trip card to navigate to a detailed Trip Summary page.
- FR-010: The Trip Summary page must display the grand total cost and a list of all individual expenses.

### 3.3. Expense Management
- FR-011: Users can upload one expense at a time in JPG or PNG format.
- FR-012: The system will use an integrated OpenAI service to process the uploaded image.
- FR-013: The AI service will extract the total amount and suggest an expense category.
- FR-014: The predefined categories are: Transportation, Accomodation, Food & Drink, Activities and Uncategorized.
- FR-015: After processing, the user must be shown a review screen with the extracted amount and the suggested category in a dropdown menu.
- FR-016: The user must be able to edit the amount and change the category before saving the expense.
- FR-017: If the AI service fails to extract data, the user must be presented with a form to manually enter the expense amount.
- FR-018: Users must be able to delete an individual expense from the Trip Summary page, which will require a confirmation step.
- FR-019: After a successful expense upload, the user should remain on the "Add Expense" page to facilitate adding the next receipt.

### 3.4. System & Non-Functional Requirements
- FR-020: The target processing time from file upload to result display should be under 10 seconds.
- FR-021: The user interface must display a user-friendly error message if the external AI API is unavailable or returns an error.
- FR-022: Uploaded image files (JPG/PNG) must be deleted from the server immediately after data has been successfully extracted and confirmed. User-generated trip and expense data will be retained.
- FR-023: A usage limit of 100 receipt uploads per user per month will be enforced.

## 4. Product Boundaries
### 4.1. In Scope for MVP
- User registration and authentication (login/logout).
- Creation and management of trips with a single, fixed currency per trip.
- Single file upload for expenses (JPG and PNG formats only).
- AI-powered data extraction for amount and category.
- Manual correction and entry of expense data.
- Dashboard view of all trips and a detailed summary view for each trip.
- A simple web application interface.

### 4.2. Out of Scope for MVP
- Batch/multi-file expense uploading.
- Support for other file formats (e.g., PDF, CSV, XLSX).
- Multi-currency conversion within a single trip.
- Expense splitting, group travel features, or collaborative trip management.
- Budgeting features (e.g., setting a budget and tracking against it).
- Native mobile applications (iOS/Android).
- Paid subscriptions or premium features.
- Advanced reporting or data export functionalities.

## 5. User Stories
- ID: US-001
- Title: New User Account Registration
- Description: As a new user, I want to create an account with my email and a password so that I can securely access the application and save my trip information.
- Acceptance Criteria:
  - 1. The user can navigate to a sign-up page.
  - 2. The user must provide a valid email address and a password.
  - 3. Upon successful registration, the user is automatically logged in.
  - 4. The user is redirected to the main dashboard page after registration.

- ID: US-002
- Title: Existing User Login
- Description: As a returning user, I want to log in to my account so that I can access my saved trips and add new expenses.
- Acceptance Criteria:
  - 1. The user can enter their registered email and password on a login page.
  - 2. If credentials are valid, the user is granted access and redirected to the "My Trips" dashboard.
  - 3. If credentials are invalid, an appropriate error message is displayed.

- ID: US-003
- Title: User Logout
- Description: As a logged-in user, I want to log out of the application to ensure my session is securely closed.
- Acceptance Criteria:
  - 1. A logout button or link is available within the application.
  - 2. Clicking logout terminates the user's session.
  - 3. The user is redirected to the login page after logging out.

- ID: US-004
- Title: First-Time User Onboarding
- Description: As a new user who has just logged in for the first time, I want to see a clear prompt on the dashboard to guide me on how to start.
- Acceptance Criteria:
  - 1. If a user has no trips created, the dashboard displays a welcome message.
  - 2. A prominent "Create Your First Trip" button is displayed in this empty state.

- ID: US-005
- Title: Create a New Trip
- Description: As a logged-in user, I want to create a new trip by giving it a name and selecting its currency so I can start adding expenses to it.
- Acceptance Criteria:
  - 1. The user can access a "New Trip" creation form from the dashboard.
  - 2. The form contains an input field for "Trip Name" and a dropdown for "Trip Currency".
  - 3. The "Trip Name" field is mandatory.
  - 4. The "Trip Name" cannot be a duplicate of another trip name for the same user.
  - 5. The "Trip Name" is limited to 70 characters.
  - 6. Upon successful creation, the new trip appears on the "My Trips" dashboard.

- ID: US-006
- Title: View Trip Dashboard
- Description: As a returning user, I want to see a summary of all my trips on a central dashboard so I can get a quick overview of my travels.
- Acceptance Criteria:
  - 1. The dashboard displays a "card" for each trip created by the user.
  - 2. Each card clearly shows the Trip Name, Total Cost, and the number of expenses logged.

- ID: US-007
- Title: Add an Expense Successfully
- Description: As a user, I want to upload a receipt image for a specific trip and have the system automatically extract the details so I can add it to my total cost.
- Acceptance Criteria:
  - 1. From a Trip Summary page, the user can initiate an expense upload.
  - 2. The user can select a JPG or PNG file from their device.
  - 3. After processing, a review screen shows the amount extracted by the AI and the category it suggests in a dropdown.
  - 4. The user can save the expense, which adds it to the trip's expense list.
  - 5. The total cost for the trip is updated on both the summary and dashboard pages.
  - 6. The user remains on the "Add Expense" screen to allow for another upload.

- ID: US-008
- Title: Manually Correct an Expense
- Description: As a user, I want to be able to edit the amount and change the category of an expense after the AI has processed it, to ensure accuracy.
- Acceptance Criteria:
  - 1. On the expense review screen, the amount field is editable.
  - 2. The category is a dropdown menu, allowing the user to select a different option.
  - 3. Saving the expense after making changes correctly records the new data.

- ID: US-009
- Title: Manually Add an Expense After AI Failure
- Description: As a user, if the system fails to read my receipt, I want to be able to manually enter the expense amount so I am not blocked from tracking my spending.
- Acceptance Criteria:
  - 1. If the AI service returns an error or cannot extract data, the user is notified.
  - 2. The user is presented with a form to manually type in the expense amount.
  - 3. The user can still select a category from the dropdown.
  - 4. Saving the form adds the expense to the trip as if it were processed automatically.

- ID: US-010
- Title: Delete an Expense
- Description: As a user, I want to be able to delete an expense from my trip's summary page in case I added it by mistake or it's a duplicate.
- Acceptance Criteria:
  - 1. Each expense listed on the Trip Summary page has a visible delete icon.
  - 2. Clicking the delete icon prompts the user with a confirmation message (e.g., "Are you sure you want to delete this expense?").
  - 3. Confirming the action permanently removes the expense from the trip.
  - 4. The trip's total cost is immediately recalculated and updated.

- ID: US-011
- Title: Handle API Unavailability
- Description: As a user trying to upload a receipt, I want to receive a clear error message if the data extraction service is temporarily unavailable.
- Acceptance Criteria:
  - 1. If the OpenAI API call fails due to network issues or service downtime, the upload process is halted.
  - 2. A user-friendly error message (e.g., "Sorry, we couldn't read your file right now. Please try again.") is displayed to the user.
  - 3. The user is not left in a perpetual loading state.

- ID: US-012
- Title: Enforce Monthly Upload Limit
- Description: As a user, I want to be notified when I have reached my monthly upload limit to understand why I cannot add more expenses.
- Acceptance Criteria:
  - 1. The system tracks the number of successful uploads per user per calendar month.
  - 2. When a user attempts their 101st upload in a month, the upload is blocked.
  - 3. A clear message is displayed informing the user they have reached their monthly limit of 100 uploads.

## 6. Success Metrics
### 6.1. Primary Metric
- Successful Calculation Rate: This measures the core value delivery of the product.
- Calculation: `(Number of users who successfully add at least one expense to a trip) / (Total number of users who create an account)`
- The rate must be at least 0.9

### 6.2. Secondary Metric
- Extraction Accuracy: This measures the performance and reliability of the core AI technology.
- Calculation: `Internal monitoring of the percentage of expenses that are manually corrected by users. The goal is to decrease this percentage over time through improvements.`
```
