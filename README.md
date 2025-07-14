ExamCepe Manager
Overview
ExamCepe Manager is a web application built using Angular for the front-end and CodeIgniter for the back-end. This project aims to manage school and student data, including adding schools, editing school details, and tracking student results.
Features

Add and manage school information (e.g., school number, designation, address).
View and edit a list of schools and students.
Display student results with admission status (Admis, Délibéré, Recalé).
User-friendly interface with search functionality.

Screenshots
./docs/screenshoot_3.png
./docs/screenshoot_2.png
./docs/screenshoot_1.png

Prerequisites

Node.js and npm (for Angular)
PHP (for CodeIgniter)
Composer (for PHP dependencies)
A web server (e.g., Apache or Nginx)

Installation
Back-end (CodeIgniter)

Navigate to the backend directory.
Run composer install to install dependencies.
Configure the database in application/config/database.php.
Set up your web server to point to the backend directory.

Front-end (Angular)

Navigate to the frontend directory.
Run npm install to install dependencies.
Run ng serve to start the development server (http://localhost:4200).

Usage

Add a new school by navigating to the "École" section and clicking "Ajouter une école".
Edit or delete existing schools via the "Éditer l'école" or "Supprimer l'école" options.
View student results under the "Résultat" section, with filters for admission status.

Contributing

Fork the repository.
Create a new branch (git checkout -b feature-branch).
Commit your changes (git commit -m "Add new feature").
Push to the branch (git push origin feature-branch).
Open a pull request.

License
This project is licensed under the MIT License.
