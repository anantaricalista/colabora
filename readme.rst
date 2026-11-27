#########
Co-Labora
#########

Co-Labora is a standalone employee and project management application for
service companies. It is developed separately from Co-Balance; integration is
optional and is not required for standalone use.

Current foundation
==================

This repository contains CodeIgniter 3.1.13, an initial authentication and
account-management foundation, and AdminLTE 4.0.0-rc3 CSS/JavaScript assets.
Payroll, employee records, service catalog, and Co-Balance integration
workflows are still being developed and their business rules are being agreed
incrementally. The initial project/task workflow and confirmed business
decisions are documented in ``docs/business-decisions.md``.

Installation and operations
===========================

See ``docs/installation.md`` for setup and ``docs/backup-recovery.md`` for the
initial backup and recovery procedure. Production deployments must use HTTPS,
server-managed secrets, and a separate database for each company installation.

Runtime note
============

The current implementation target is PHP 8.0.0 as requested for development.
PHP 8.0 is no longer receiving security updates; use only in a controlled test
environment and plan a move to a supported PHP release before production.
