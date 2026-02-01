# Lab-Booking-Management-System

Lab Booking Management System is a role-based web application designed to manage and optimize laboratory resource scheduling in an academic environment.

The system provides a structured workflow where students can request access to laboratory resources through predefined time slots, and administrators can review, approve, or reject those requests to prevent scheduling conflicts and ensure fair resource utilization.

---

## Project Overview

In many institutions, lab scheduling is handled manually or through informal coordination, which often results in overlapping bookings, underutilized resources, and poor visibility into lab usage. This project addresses those issues by introducing a centralized, database-driven booking system with role-based access control.

---

## Core Features

- Role-based authentication and authorization (Student / Admin)
- Secure login and session management
- Time-slot–based laboratory booking
- Automatic conflict detection to prevent overlapping reservations
- Admin approval and rejection workflow
- Status-driven booking lifecycle (Pending, Approved, Rejected)
- Separate dashboards for students and administrators
- Centralized backend logic for booking validation
- Persistent storage using a relational database

---

## Workflow Design

1. Users register and log in to the system.
2. Students submit booking requests by selecting a lab resource, date, and time slot.
3. The system validates requests against existing bookings to detect conflicts.
4. Administrators review pending requests and approve or reject them.
5. Booking status updates are reflected across the system in real time.
6. Approved bookings reserve the selected resource for the specified time slot.

---

## Technology Stack

- Backend: PHP
- Database: MySQL
- Frontend: HTML, CSS

---

## Design Focus

- Clear separation of concerns between frontend and backend
- Role-based access control to enforce permissions
- Status-driven workflows to maintain consistency
- Simple, maintainable architecture suitable for future scaling

---

## Use Case

This system is suitable for colleges, universities, or training institutions that require controlled and conflict-free access to shared laboratory resources.

---

## Limitations

- Basic user interface styling
- Manual administrative approval required
- Designed primarily for local or intranet deployment

These constraints are intentional to emphasize correctness, clarity, and core system behavior.

---

## Disclaimer

This project is developed for educational purposes to demonstrate role-based access control, workflow management, and database-driven web application design.
