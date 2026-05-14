<?php

session_start();
include "../connection.php";

require '../config.php';

use Cloudinary\Api\Upload\UploadApi;

if (isset($_SESSION["user_id"])) {
    if (isset($_POST["submit"])) {  // For Add Event
        $pdf_url = null;

        $event_name = $_POST["event_name"];
        $event_venue = $_POST["event_venue"];

        $event_type = $_POST["event_type"];
        $maximum_participants = $_POST["total_members"];

        $event_start_date = $_POST["event_start_date"];
        $event_start_time = $_POST["event_start_time"];
        $event_end_date = $_POST["event_end_date"];
        $event_end_time = $_POST["event_end_time"];
        $event_category = $_POST["event_category"];
        $registration_start_date = $_POST["registration_start_date"];
        $registration_start_time = $_POST["registration_start_time"];
        $registration_end_date = $_POST["registration_end_date"];
        $registration_end_time = $_POST["registration_end_time"];
        $registration_fee = $_POST["registration_fee"];
        $organized_by = $_POST["organized_by"];
        $created_by = $_SESSION["user_id"];
        $faculty = $_POST["faculty"];
        $student = $_POST["student"];
        $contact = $_POST["contact"];

        $clean_event_name = preg_replace('/[^A-Za-z0-9]/', '', $event_name);
        $event_id = "EVENT-" . strtoupper($clean_event_name) . "-" . date("Ym") . "-" . rand(100, 999);

        if (isset($_FILES["event_rules"]) && $_FILES["event_rules"]['error'] == 0) {
            $original_name = $_FILES['event_rules']['name']; // original file name
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION); // get extension

            // Clean event name (remove spaces & special characters)
            $clean_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $event_name);

            // Create new file name
            $new_file_name = $clean_name . "_" . time();

            $upload = (new UploadApi())->upload(
                $_FILES['event_rules']['tmp_name'],
                [
                    'resource_type' => 'auto',
                    'folder' => 'event_rules',
                    'public_id' => $new_file_name,
                    'format' => $file_extension
                ]
            );
            $pdf_url = $upload['secure_url'];
        }

        // Inserting the Data into event table
        $event_data_insert = $connection->prepare("INSERT INTO event(event_id,
                                                                    event_name,
                                                                    event_venue,
                                                                    event_type,
                                                                    maximum_participants,
                                                                    event_start_date,
                                                                    event_start_time,
                                                                    event_end_date,
                                                                    event_end_time,
                                                                    event_category,
                                                                    registration_start_date,
                                                                    registration_start_time,
                                                                    registration_end_date,
                                                                    registration_end_time,
                                                                    registration_fee,
                                                                    event_rules,
                                                                    organized_by,
                                                                    created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $event_data_insert->bind_param(
            "ssssisssssssssssss",
            $event_id,
            $event_name,
            $event_venue,
            $event_type,
            $maximum_participants,
            $event_start_date,
            $event_start_time,
            $event_end_date,
            $event_end_time,
            $event_category,
            $registration_start_date,
            $registration_start_time,
            $registration_end_date,
            $registration_end_time,
            $registration_fee,
            $pdf_url,
            $organized_by,
            $created_by
        );

        $event_data_insert->execute();

        // Inserting the Data into faculty_coordinators table
        $faculty_coordinator_insert = $connection->prepare("INSERT INTO faculty_coordinators(event_id,user_id) VALUES(?,?)");
        for ($i = 0; $i < count($faculty); $i++) {
            $faculty_user_id = $faculty[$i];
            $faculty_coordinator_insert->bind_param("ss", $event_id, $faculty_user_id);
            $faculty_coordinator_insert->execute();
        }

        // Inserting the Data into student_coordinators table
        $student_coordinators_insert = $connection->prepare("INSERT INTO student_coordinators(event_id,student_name,student_contact) VALUES(?,?,?)");
        for ($i = 0; $i < count($student); $i++) {
            $student_name = $student[$i];
            $student_contact = $contact[$i];
            $student_coordinators_insert->bind_param("sss", $event_id, $student_name, $student_contact);
            $student_coordinators_insert->execute();
        }

        header("location:AddEvent.php?successMsg=Event Added Successfully!");
        exit();
    }

    if (isset($_POST["update"])) {  // For Edit Event
        $event_id = $_POST["event_id"];

        $event_name = $_POST["event_name"];
        $event_venue = $_POST["event_venue"];

        $event_type = $_POST["event_type"];
        $maximum_participants = $_POST["total_members"];

        $event_start_date = $_POST["event_start_date"];
        $event_start_time = $_POST["event_start_time"];
        $event_end_date = $_POST["event_end_date"];
        $event_end_time = $_POST["event_end_time"];
        $event_category = $_POST["event_category"];
        $registration_start_date = $_POST["registration_start_date"];
        $registration_start_time = $_POST["registration_start_time"];
        $registration_end_date = $_POST["registration_end_date"];
        $registration_end_time = $_POST["registration_end_time"];
        $registration_fee = $_POST["registration_fee"];
        $organized_by = $_POST["organized_by"];
        $created_by = $_SESSION["user_id"];

        $faculty = $_POST["faculty"];
        $student = $_POST["student"];
        $contact = $_POST["contact"];

        if (isset($_FILES["event_rules"]) && $_FILES["event_rules"]['error'] == 0) {
            $original_name = $_FILES['event_rules']['name']; // original file name
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION); // get extension

            // Clean event name (remove spaces & special characters)
            $clean_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $event_name);

            // Create new file name
            $new_file_name = $clean_name . "_" . time();

            $upload = (new UploadApi())->upload(
                $_FILES['event_rules']['tmp_name'],
                [
                    'resource_type' => 'auto',
                    'folder' => 'event_rules',
                    'public_id' => $new_file_name,
                    'format' => $file_extension
                ]
            );
            $pdf_url = $upload['secure_url'];

            $update_event_table = $connection->prepare("UPDATE event SET
                event_name = ?,
                event_venue = ?,
                event_type = ?,
                maximum_participants = ?,
                event_start_date = ?,
                event_start_time = ?,
                event_end_date = ?,
                event_end_time = ?,
                event_category = ?,
                registration_start_date = ?,
                registration_start_time = ?,
                registration_end_date = ?,
                registration_end_time = ?,
                registration_fee = ?,
                event_rules = ?,
                organized_by = ?
                WHERE event_id = ?");

            $update_event_table->bind_param(
                "sssisssssssssssss",
                $event_name,
                $event_venue,
                $event_type,
                $maximum_participants,
                $event_start_date,
                $event_start_time,
                $event_end_date,
                $event_end_time,
                $event_category,
                $registration_start_date,
                $registration_start_time,
                $registration_end_date,
                $registration_end_time,
                $registration_fee,
                $pdf_url,
                $organized_by,
                $event_id
            );
        } else {
            // Updating the event table
            $update_event_table = $connection->prepare("UPDATE event SET
                event_name = ?,
                event_venue = ?,
                event_type = ?,
                maximum_participants = ?,
                event_start_date = ?,
                event_start_time = ?,
                event_end_date = ?,
                event_end_time = ?,
                event_category = ?,
                registration_start_date = ?,
                registration_start_time = ?,
                registration_end_date = ?,
                registration_end_time = ?,
                registration_fee = ?,
                organized_by = ?
                WHERE event_id = ?");

            $update_event_table->bind_param(
                "sssissssssssssss",
                $event_name,
                $event_venue,
                $event_type,
                $maximum_participants,
                $event_start_date,
                $event_start_time,
                $event_end_date,
                $event_end_time,
                $event_category,
                $registration_start_date,
                $registration_start_time,
                $registration_end_date,
                $registration_end_time,
                $registration_fee,
                $organized_by,
                $event_id
            );
        }
        $update_event_table->execute();

        $connection->query("DELETE FROM faculty_coordinators WHERE event_id='$event_id'");  // Delete the Faculty Data from faculty_coordinators Table
        $connection->query("DELETE FROM student_coordinators WHERE event_id='$event_id'");  // Delete the Faculty Data from student_coordinators Table

        // Inserting the New Faculty Data to faculty_coordinators table
        $faculty_coordinator_insert = $connection->prepare("INSERT INTO faculty_coordinators(event_id,user_id) VALUES(?,?)");
        for ($i = 0; $i < count($faculty); $i++) {
            $faculty_user_id = $faculty[$i];
            $faculty_coordinator_insert->bind_param("ss", $event_id, $faculty_user_id);
            $faculty_coordinator_insert->execute();
        }

        // Inserting the New Faculty Data to student_coordinators table
        $student_coordinators_insert = $connection->prepare("INSERT INTO student_coordinators(event_id,student_name,student_contact) VALUES(?,?,?)");
        for ($i = 0; $i < count($student); $i++) {
            $student_name = $student[$i];
            $student_contact = $contact[$i];
            $student_coordinators_insert->bind_param("sss", $event_id, $student_name, $student_contact);
            $student_coordinators_insert->execute();
        }

        header("location:AddEvent.php?successMsg=Event Updated Successfully!&event_id=$event_id");
        exit();
    }
} else {
    header("location:../login.php");
    exit();
}
