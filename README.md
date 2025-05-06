# Coachini

A web-based coaching platform developed as part of the PIDEV 3A course at Esprit School of Engineering. Coachini connects coaches, investors, event creators, and users to manage sessions, sell products, participate in events, view offers and schedules, and handle complaints.

## Description

Coachini is an innovative web application designed to streamline coaching management and interactions among various stakeholders. Developed for the PIDEV 3A course at [Esprit School of Engineering](https://esprit.tn), the platform extends beyond coaches and clients to include investors, event creators, and users. Its features encompass product sales, event participation, browsing coaches' offers, schedules, or products, and a dedicated complaints section. Coachini provides a responsive and collaborative interface to meet the needs of all its users.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Contributions](#contributions)
- [License](#license)
- [Acknowledgements](#acknowledgements)

## Installation

To install Coachini locally, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/your-username/coachini.git
   cd coachini
   ```

2. **Install dependencies**:
   Ensure [Composer](https://getcomposer.org/) is installed, then run:
   ```bash
   composer install
   ```

3. **Set up the environment**:
    - Copy the `.env.example` file to `.env` and configure your database settings (e.g., MySQL credentials).
   ```bash
   cp .env.example .env
   ```

4. **Set up the database**:
   Run the following commands to create and populate the database:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Start the server**:
   If using WAMP/XAMPP:
    - Place the project in the `www` (WAMP) or `htdocs` (XAMPP) directory.
    - Start Apache and MySQL from the WAMP/XAMPP interface.
    - Access the project at `http://localhost/coachini/public`.

   Alternatively, use Symfony's built-in server:
   ```bash
   symfony server:start
   ```

## Usage

Once installed, access Coachini via your browser at `http://localhost:8000` (or the appropriate URL based on your setup). Key actions include:

- **Registration/Login**: Create an account or log in as a coach, investor, event creator, or user.
- **Session Management**: Coaches can create sessions, and users can book them.
- **Product Sales**: Purchase coaching-related products (e.g., programs or equipment).
- **Event Participation**: Register for events organized by creators.
- **Browsing**: View coaches' offers, schedules, or available products.
- **Complaints**: Submit complaints through the dedicated section.

## Features

- **Secure Authentication**: Registration and login for various user types (coaches, investors, event creators, users).
- **Session Management**: Scheduling and booking of coaching sessions.
- **Product Sales**: Integrated store for purchasing coaching-related products.
- **Events**: Registration and participation in organized events.
- **Offer Browsing**: Access to schedules, coaches' offers, and products.
- **Complaints**: System for submitting and managing complaints.
- **Responsive Design**: Interface optimized for desktops and mobile devices.

## Technologies Used

### Frontend
- **Bootstrap**: For a modern and responsive interface.
- **JavaScript**: For dynamic client-side interactions.

### Backend
- **Symfony 6.4**: PHP framework for robust backend logic.
- **MySQL**: Relational database for storing user, session, and product data.

### Other Tools
- **Composer**: Dependency management for PHP.
- **Doctrine ORM**: For database interactions.

## Contributions

We welcome contributions to Coachini! The following individuals have contributed to the project:

- Farah Ben Yedder - Development and management of user administration features.
- Houssem Laabidi - Design and implementation of event management for seamless organization.
- Maissa Maalej - Creation and optimization of session and schedule management tools.
- Ghada Maalej - Implementation and management of product sales and administration features.
- Hamza Lassoued - Development of the complaints management system for an enhanced user experience.
- Baha Arbi - Implementation of offer management features to facilitate interactions with coaches.

To contribute:

1. **Fork the project**: Click the "Fork" button on the [Coachini repository](https://github.com/your-username/coachini).
2. **Clone your fork**:
   ```bash
   git clone https://github.com/your-username/coachini.git
   cd coachini
   ```
3. **Create a branch**:
   ```bash
   git checkout -b feature/your-feature
   ```
4. **Commit changes**:
   ```bash
   git add .
   git commit -m "Description of your feature"
   ```
5. **Push and create a pull request**:
   ```bash
   git push origin feature/your-feature
   ```
   Then, submit a pull request via GitHub.

## License

This project currently has no specific license. Contact the authors for more information.

## Acknowledgements

This project was developed under the guidance of Ms. Soumaya Sassi at Esprit School of Engineering as part of the PIDEV 3A course. Special thanks to our peers and mentors for their support.