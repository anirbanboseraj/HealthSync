<?php

$current_page = basename($_SERVER['PHP_SELF']);

?>

<aside class="patient-sidebar">


    <!-- =====================================================
         LOGO
    ====================================================== -->

    <div class="sidebar-logo">

        <div class="logo-icon">

            <i class="fa-solid fa-heart-pulse"></i>

        </div>


        <div class="logo-text">

            <h2>
                Health<span>Sync</span>
            </h2>

            <p>
                Patient Portal
            </p>

        </div>

    </div>



    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <nav class="patient-nav">


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="patient-nav-link <?php
                echo ($current_page === 'dashboard.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-house nav-icon"></i>

            <span class="nav-text">
                Dashboard
            </span>

        </a>



        <!-- DOCTORS -->

        <a
            href="doctors.php"
            class="patient-nav-link <?php
                echo ($current_page === 'doctors.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-user-doctor nav-icon"></i>

            <span class="nav-text">
                Doctors
            </span>

        </a>



        <!-- MY APPOINTMENTS -->

        <a
            href="my-appointments.php"
            class="patient-nav-link <?php
                echo ($current_page === 'my-appointments.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-calendar-check nav-icon"></i>

            <span class="nav-text">
                My Appointments
            </span>

        </a>



        <!-- PRESCRIPTIONS -->

        <a
            href="prescriptions.php"
            class="patient-nav-link <?php
                echo ($current_page === 'prescriptions.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-prescription-bottle-medical nav-icon"></i>

            <span class="nav-text">
                Prescriptions
            </span>

        </a>



        <!-- MEDICAL RECORDS -->

        <a
            href="medical-records.php"
            class="patient-nav-link <?php
                echo ($current_page === 'medical-records.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-file-medical nav-icon"></i>

            <span class="nav-text">
                Medical Records
            </span>

        </a>



        <!-- NOTIFICATIONS -->

        <a
            href="notifications.php"
            class="patient-nav-link <?php
                echo ($current_page === 'notifications.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-bell nav-icon"></i>

            <span class="nav-text">
                Notifications
            </span>

        </a>



        <!-- MY PROFILE -->

        <a
            href="profile.php"
            class="patient-nav-link <?php
                echo ($current_page === 'profile.php')
                    ? 'active'
                    : '';
            ?>"
        >

            <i class="fa-solid fa-user nav-icon"></i>

            <span class="nav-text">
                My Profile
            </span>

        </a>


    </nav>



    <!-- =====================================================
         BOTTOM SECTION
    ====================================================== -->

    <div class="sidebar-bottom">


        <!-- PATIENT USER -->

        <div class="sidebar-user">


            <div class="sidebar-user-icon">

                <i class="fa-solid fa-user"></i>

            </div>


            <div class="sidebar-user-info">

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION['fullname'] ?? 'Patient'
                    );

                    ?>

                </strong>


                <span>
                    Patient
                </span>

            </div>


        </div>



        <!-- LOGOUT -->

        <a
            href="../logout.php"
            class="patient-nav-link logout-link"
        >

            <i class="fa-solid fa-right-from-bracket nav-icon"></i>

            <span class="nav-text">
                Logout
            </span>

        </a>


    </div>


</aside>