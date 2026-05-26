# HilkVisual Shared API

This API is the backend contract that both the admin web app and the React Native app can use.

## Base URL

If the project is running inside XAMPP:

`http://localhost/admin_hilkvisual/api/index.php`

## Endpoints

- `GET ?action=health`
- `GET ?action=dashboard`
- `GET ?action=bookings`
- `POST ?action=bookings`
- `PATCH ?action=bookings`
- `GET ?action=uploads`
- `POST ?action=uploads`
	- Accepts either JSON metadata (existing behavior) or multipart/form-data with a `file` field.
		- Form fields supported: `user_id`, `booking_id`.
		- Uploaded files are saved to `uploads/clients/<user_id>/` or `uploads/mobile/` and a DB record is created.
- `GET ?action=photoshoots`
- `POST ?action=photoshoots`
- `GET ?action=reports`

## Database

Import `database/schema.sql` into MySQL, then update `api/config.php` with your credentials.
