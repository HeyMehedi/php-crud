<?php
define(DB_NAME, 'data/db.txt');

// Get Content From File
function contentGet()
{
    $unserializedData = file_get_contents(DB_NAME);
    $students = unserialize($unserializedData);
    return $students;
}

// Update Content in File
function contentPut($students)
{
    $serializedData = serialize($students);
    file_put_contents(DB_NAME, $serializedData, LOCK_EX);
}

// Dummy Students Import
function seed()
{
    $students = array(
        array(
            'id' => 1,
            'fname' => 'Mehedi',
            'lname' => 'Hasan',
            'roll' => 10,
        ),
        array(
            'id' => 2,
            'fname' => 'Shahadat',
            'lname' => 'Hossain',
            'roll' => 20,
        ),
        array(
            'id' => 3,
            'fname' => 'Faruk',
            'lname' => 'Ahmed',
            'roll' => 15,
        ),
    );
    contentPut($students);
}

// Students Report
function generateReport()
{
    $students = contentGet();
    ?>
    <table>
        <tr>
            <th>#ID</th>
            <th>Roll</th>
            <th>Name</th>
            <?php if (isRole('admin') || isRole('editor')): ?>
                <th>Action</th>
            <?php endif;?>
        </tr>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?php printf('%s', $student['id'])?></td>
            <td><?php printf('%s', $student['roll'])?></td>
            <td><?php printf('%s %s', $student['fname'], $student['lname'])?></td>
            <?php if (isRole('admin')): ?>
               <td><?php printf('<a href="/?task=edit&id=%s">Edit</a> | <a class="delete" href="/?task=delete&id=%s">Delete</a>', $student['id'], $student['id']);?></td>
            <?php elseif (isRole('editor')): ?>
                <td><?php printf('<a href="/?task=edit&id=%s">Edit</a>', $student['id']);?></td>
            <?php endif;?>
        </tr>
        <?php endforeach;?>
    </table>
<?php
}

// Get New Id
function getNewId($students)
{
    $maxId = max(array_column($students, 'id'));
    return $maxId + 1;
}

// Add Student
function addStudent($fname, $lname, $roll)
{
    $found = false;
    $students = contentGet();
    foreach ($students as $_student) {
        if ($_student['roll'] == $roll) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        $newId = getNewId($students);
        $student = array(
            'id' => $newId,
            'fname' => $fname,
            'lname' => $lname,
            'roll' => $roll,
        );
        array_push($students, $student);
        contentPut($students);
        return true;
    }
    return false;
}

// Get Student
function getStudent($id)
{
    $found = false;
    $students = contentGet();
    foreach ($students as $student) {
        if ($student['id'] == $id) {
            return $student;
        }
    }
    return false;
}

// Update Student
function updateStudent($id, $fname, $lname, $roll)
{
    $found = false;
    $students = contentGet();
    foreach ($students as $_student) {
        if ($_student['roll'] == $roll && $_student['id'] != $id) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $students[$id - 1]['fname'] = $fname;
        $students[$id - 1]['lname'] = $lname;
        $students[$id - 1]['roll'] = $roll;
        contentPut($students);
        return true;
    }
    return false;
}

// Delete Student
function deleteStudent($id)
{
    $students = contentGet();
    $i = 0;
    foreach ($students as $offset => $student) {
        if ($student['id'] == $id) {
            unset($students[$offset]);
        }
        $i++;
    }
    contentPut($students);
}

// Error Messages
function errorMessge($error)
{
    switch ($error) {
        case '1':
            $message = "Duplicate Roll Number!";
            break;

        case '2':
            $message = "Seeding Done!";
            break;
        case '3':
            $message = "Something is wrong!";
            break;

        default:
            $message = '';
            break;
    }
    return $message;
}

// Has Role admin or editor
function isRole($role)
{
    return ($role == $_SESSION['role']);
}
function hasPrivilege()
{
    return (isRole('admin') || isRole('editor'));
}