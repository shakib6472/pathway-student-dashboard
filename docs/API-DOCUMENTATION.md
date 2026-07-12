# Pathway Student Dashboard — API & Webhook Documentation

Integration guide for connecting the main website (pathway2da.com) with the LMS portal (learn.pathway2da.com).

**Author:** Shakib Shown · **Plugin:** Pathway Student Dashboard · **API version:** v1

---

## 1. Authentication

Every request must include the secret API key in a header:

```
X-Pathway-Api-Key: pda_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

- Find or regenerate the key in **WP Admin → Pathway Settings** on the LMS site.
- Requests with a missing/wrong key receive `401 Unauthorized`.
- Always call the API over **HTTPS**. Never expose the key in front-end JavaScript — call these endpoints from the main site's server (PHP), not from the browser.

---

## 2. Endpoints

### 2.1 List courses

```
GET https://learn.pathway2da.com/wp-json/pathway/v1/courses
```

Returns all published courses (metadata only — no lessons/topics, no pricing).

**Response — `200 OK`:**

```json
[
  {
    "id": 156,
    "title": "Texas 80-Hour Dental Assistant Training",
    "slug": "texas-80-hour-dental-assistant-training",
    "url": "https://learn.pathway2da.com/courses/texas-80-hour-dental-assistant-training/",
    "excerpt": "State-approved 80-hour dental assistant training…",
    "thumbnail": "https://learn.pathway2da.com/wp-content/uploads/2026/07/texas-course.jpg",
    "hours": 80,
    "has_certificate": true,
    "created": "2026-06-01 10:00:00",
    "modified": "2026-07-10 08:30:00"
  }
]
```

### 2.2 Single course

```
GET https://learn.pathway2da.com/wp-json/pathway/v1/courses/{id}
```

Same object as above for one course. Unknown/unpublished id → `404`.

### 2.3 Enrollment webhook

```
POST https://learn.pathway2da.com/wp-json/pathway/v1/enroll
Content-Type: application/json
X-Pathway-Api-Key: <key>
```

Call this from the main website **after a successful payment**. The LMS will:

1. Create the student account (or reuse it if the email already exists — the existing password is never changed).
2. Enroll the student in the course.
3. Save the student's US state and generate a Student ID.
4. Email the student a welcome message with a **login link** (passwords are never emailed).
5. Add a bell notification inside the dashboard.

**Request body:**

| Field        | Type    | Required            | Notes                                              |
|--------------|---------|---------------------|----------------------------------------------------|
| `email`      | string  | yes                 | Student's email, becomes the username.             |
| `first_name` | string  | yes                 |                                                    |
| `last_name`  | string  | no                  |                                                    |
| `password`   | string  | for **new** users   | Min 8 characters. Ignored for existing accounts.   |
| `course_id`  | integer | yes                 | A course `id` from the courses endpoint.           |
| `state`      | string  | no                  | 2-letter US state code, e.g. `"TX"`, `"CA"`.       |

**Example request:**

```json
{
  "email": "sarah@example.com",
  "first_name": "Sarah",
  "last_name": "Miller",
  "password": "chosen-at-checkout-123",
  "course_id": 156,
  "state": "TX"
}
```

**Response — `200 OK`:**

```json
{
  "success": true,
  "user_id": 42,
  "created": true,
  "already_enrolled": false,
  "enrolled": true,
  "student_id": "PDA-2026-0042",
  "course_id": 156
}
```

- `created` — `true` when a new account was made, `false` for an existing student.
- `already_enrolled` — `true` when the student already had this course (no duplicate email is sent).
- The endpoint is **idempotent**: sending the same request twice is safe.

**Error responses:**

| Status | Code                                | Meaning                                    |
|--------|-------------------------------------|--------------------------------------------|
| 401    | `pathway_dash_invalid_key`          | Missing or wrong API key.                  |
| 400    | `pathway_dash_invalid_email`        | Email missing or malformed.                |
| 400    | `pathway_dash_missing_name`         | `first_name` missing.                      |
| 400    | `pathway_dash_weak_password`        | New account without a valid password.      |
| 404    | `pathway_dash_course_not_found`     | Unknown/unpublished `course_id`.           |
| 500    | `pathway_dash_user_creation_failed` | WordPress could not create the user.       |

Error body example:

```json
{
  "code": "pathway_dash_weak_password",
  "message": "password is required for new accounts (minimum 8 characters).",
  "data": { "status": 400 }
}
```

---

## 3. Sample code (main website)

### 3.1 PHP / WordPress — send an enrollment after payment

```php
/**
 * Enroll a customer on the LMS after successful payment.
 * Call from your payment-success hook on the main site.
 */
function pathway_main_send_enrollment( $email, $first_name, $last_name, $password, $course_id, $state ) {

	$response = wp_remote_post(
		'https://learn.pathway2da.com/wp-json/pathway/v1/enroll',
		array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type'      => 'application/json',
				'X-Pathway-Api-Key' => PATHWAY_LMS_API_KEY, // define this in wp-config.php.
			),
			'body'    => wp_json_encode(
				array(
					'email'      => $email,
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'password'   => $password,
					'course_id'  => $course_id,
					'state'      => $state,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		// Network failure — log and retry later (see §4).
		error_log( 'LMS enroll failed: ' . $response->get_error_message() );
		return false;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || empty( $body['success'] ) ) {
		error_log( 'LMS enroll rejected (' . $code . '): ' . wp_remote_retrieve_body( $response ) );
		return false;
	}

	return $body; // Contains user_id, student_id, created, etc.
}
```

Store the key in the main site's `wp-config.php`:

```php
define( 'PATHWAY_LMS_API_KEY', 'pda_xxxxxxxx…' );
```

### 3.2 PHP / WordPress — fetch the course catalog

```php
function pathway_main_get_courses() {
	$cached = get_transient( 'pathway_lms_courses' );

	if ( false !== $cached ) {
		return $cached;
	}

	$response = wp_remote_get(
		'https://learn.pathway2da.com/wp-json/pathway/v1/courses',
		array(
			'timeout' => 15,
			'headers' => array( 'X-Pathway-Api-Key' => PATHWAY_LMS_API_KEY ),
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return array();
	}

	$courses = json_decode( wp_remote_retrieve_body( $response ), true );

	set_transient( 'pathway_lms_courses', $courses, HOUR_IN_SECONDS );

	return $courses;
}
```

### 3.3 cURL — quick tests from the terminal

```bash
# List courses
curl -s https://learn.pathway2da.com/wp-json/pathway/v1/courses \
  -H "X-Pathway-Api-Key: pda_xxxxxxxx"

# Enroll a student
curl -s -X POST https://learn.pathway2da.com/wp-json/pathway/v1/enroll \
  -H "Content-Type: application/json" \
  -H "X-Pathway-Api-Key: pda_xxxxxxxx" \
  -d '{
    "email": "test.student@example.com",
    "first_name": "Test",
    "last_name": "Student",
    "password": "SuperSecret123",
    "course_id": 156,
    "state": "TX"
  }'
```

---

## 4. Integration recommendations

- **Retry on failure.** If the enroll call fails (timeout, 5xx), queue it and retry — e.g. with `wp_schedule_single_event()`. The endpoint is idempotent, so retrying is always safe.
- **Cache the catalog.** Course data rarely changes; cache `GET /courses` for an hour (see sample above).
- **Key rotation.** Regenerating the key on the LMS immediately invalidates the old one — update the main site at the same moment.
- **Testing on staging/local:** the endpoints work the same; only the domain changes.

---

## 5. Changelog

| Date       | Change                                             |
|------------|----------------------------------------------------|
| 2026-07-12 | v1: courses endpoint + enrollment webhook shipped. |
