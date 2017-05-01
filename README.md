ABOUT
=====

Pro Bono Opportunities Website (PBOW) is an open source software built as Drupal 8 distribution. It helps participating attorneys to search for and resolve cases. Site admins can import cases manually or via uploading spreadsheet files. Push via API is also supported. Knowledge of Drupal content management system is a plus to effectively configure and manage your PBOW site.


INSTALLATION
============

1. Choose language: English

2. Verify requirements: Some minor warnings may be ignored -- like one for Opcache, which is good for production environment, but not necessary during development. Proceed with "continue anyway" at the bottom of the page if necessary.

3. Set up database: Enter database info. Create database if not already done

4. Install site: wait while it installs modules and themes

5. Configure site: Enter your information to create the admin user


CONFIGURATION
=============

Menus
-----

These are suggested menu items for navigation_admin menu (/admin/structure/menu/manage/navigation-admin)

|          Menu          |       Path       |
|------------------------|------------------|
| Dashboard              | /dashboard       |
| Case Management        | /case-management |
| Import                 | /import          |
| Search Available Cases | /search-case     |
| Subscribed Tags        | /user/tags       |
| Report                 | /report          |
| Users                  | /users           |


PET templates
-------------

Configure Previewable Email Templates (PET) at /admin/structure/pets. PET ID number is significant, so the order of these templates is important.

### PET 1
- Title: Password reset for activating user
- Subject: Activate your Pro Bono Opportunities account
- Body:
  Dear [user:display-name],

  Welcome to Pro Bono Opportunities.

  To activate your account, log in by clicking this link or copying and pasting it into your browser:

  [user:pwd-reset-url]

  This link can only be used once to log in and will lead you to a page where you can set your password. This link expires after one day and nothing will happen if it's not used.

  Thank you.

  -- [site:name] team

### PET 2
- Title: Assign
- Subject: The Case ([node:field_case_id]) has been assigned to you
- Body:
  Hi [user:display-name],

  Your request to take on the following Case has been approved.

  [node:field_case_id]: [node:title]
  [node:url]

  Thank you.

  -- [site:name] team

### PET 3
- Title: Reject
- Subject: The Case ([node:field_case_id]) has been assigned to other user
- Body:
  Hi [user:display-name],

  Your requested case has been assigned to other user.

  [node:field_case_id]: [node:title]

  Thank you.

  -- [site:name] team

### PET 4
- Title: Revoke
- Subject: The Case ([node:field_case_id]) has been unassigned from you
- Body:
  Hi [user:display-name],

  Case has been unassigned from you.

  [node:field_case_id]: [node:title]
  [node:url]

  Thank you.

  -- [site:name] team

### PET 5
- Title: Monthly summary
- Subject: Your cases last month
- Body:
  Hi [user:display-name],

  This is summary of your cases.

  * My Cases
  [user:cases-assigned]

  * Pending requested Cases
  [user:cases-requested]

  * Cases matching your tags
  [user:cases-matched]

  * Cases completed
  [user:cases-completed]

  Thank you.

  -- [site:name] team
