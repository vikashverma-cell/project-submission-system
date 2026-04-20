# Project Approval Workflow System

## Features

- User authentication
- Role based access control
- Project submission
- Approval / rejection workflow
- MySQL stored procedure
- Audit logs
- Queue email notifications
- REST API
- Laravel policies
- Bulk approvals
- Filtering & sorting

---

## Setup

```bash
git clone repo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan queue:work
php artisan serve


Stored Procedure
Run SQL:
sp_approve_project()



project workflow demo link: https://www.loom.com/share/dff5c707edc740b5a1f04763aee46a36
poject code video link : https://www.loom.com/share/20daa68c13e148d8a9c77358ec5b641f