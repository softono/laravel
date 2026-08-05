# Storage API

Reference for the S3-compatible REST API — the Storage Engine described in
[`local/prd.md`](local/prd.md). This is a separate, stateless API from the
session-cookie JSON endpoints documented in [`api.md`](api.md); it lives at
the host root (no `/api` prefix), authenticates with HMAC access keys
instead of a session cookie, and returns real HTTP status codes rather
than the `{status, message, data}` envelope on success (error bodies still
use that envelope for consistency with the rest of the app).

---

## Base URL

By default, the API is served from the same host as the admin panel:

```
http://<your-host>/
```

The Super Admin can set a distinct **API Endpoint** value under
**Settings → Storage** for documentation/client-configuration purposes; it
does not change where the routes are actually served from in this
version.

### Reserved top-level names

Because the API shares one Laravel app with the admin panel and Bucket
Admin panel, a handful of top-level path segments are reserved and can
never be used as a bucket name: `login`, `register`, `logout`,
`password-forgot`, `reset-password`, `verify`, `verify-account`,
`dashboard`, `buckets`, `objects`, `api-keys`, `account`, `admin`, `up`,
`storage`, `cron`. Creating a bucket with one of these names will succeed
at the database level but the bucket will be unreachable through the S3
API (the panel routes always match first). Bucket creation should
reject these names in a future revision; for now, avoid them.

---

## Authentication

Two independent schemes, both described in `docs/local/prd.md` under
"Authentication":

### 1. HMAC request signing (writes, and reads on Private buckets)

```
Authorization: HMAC {access_key}:{signature}
Date: {RFC 1123 timestamp, e.g. "Wed, 05 Aug 2026 12:00:00 GMT"}
Content-MD5: {base64 MD5 of the request body, if present — may be omitted}
```

```
signature = base64( HMAC-SHA256(
    secret_key,
    METHOD + "\n" + PATH + "\n" + DATE + "\n" + CONTENT_MD5
) )
```

- `METHOD` is the HTTP verb, upper-case (`GET`, `PUT`, `DELETE`, ...).
- `PATH` is the request path only — **no query string**, no host, always
  starting with `/` (e.g. `/my-bucket/docs/report.pdf`).
- `DATE` is exactly the string sent in the `Date` header.
- `CONTENT_MD5` is exactly the string sent in the `Content-MD5` header, or
  an empty string if that header is omitted.

The `Date` header doubles as a replay-window check — requests outside a
configurable clock-skew tolerance (`setting.storage_clock_skew_tolerance`,
default 300 seconds) are rejected with `401`.

### 2. Pre-signed URLs (GET/HEAD only, v1)

```
GET /{bucket}/{object}?AccessKey={access_key}&Expires={unix_timestamp}&Signature={signature}
```

```
signature = base64( HMAC-SHA256(secret_key, METHOD + "\n" + PATH + "\n" + EXPIRES) )
```

Works against **Private** buckets too — that's the point: a temporary,
shareable download link without exposing the secret key. Rejected if
`Expires` is in the past or the signature doesn't match.

### 3. Anonymous access to Public buckets

A bucket flagged **Public** allows anonymous `GET`/`HEAD` (download and
listing) with no `Authorization` header at all. Every other verb — `PUT`,
`DELETE`, bucket management — always requires a valid, active access key,
regardless of the bucket's visibility.

### Getting an access key / secret key

Sign in to the Bucket Admin panel → **API Keys** → **Create Credential**.
The secret key is shown once; it's stored encrypted (not hashed — HMAC
verification needs it back in plaintext) and can't be retrieved again.
Use **Regenerate Secret** if it's lost.

---

## Rate Limiting

Per access key, via Laravel's `throttle:` middleware — configurable under
**Settings → Storage → Rate Limit** (default 60 requests/minute).
Unauthenticated public-bucket `GET`/`HEAD` requests are limited by IP
instead. Exceeding the limit returns `429`.

---

## Endpoints

### Buckets

| Method | Path | Description |
|---|---|---|
| `GET` | `/` | List your buckets |
| `PUT` | `/{bucket}` | Create a bucket |
| `DELETE` | `/{bucket}` | Delete a bucket (must be empty) |

### Objects

| Method | Path | Description |
|---|---|---|
| `GET` | `/{bucket}?list-type=2&prefix=&max-keys=&offset=&search=` | List objects |
| `PUT` | `/{bucket}/{object}` | Upload an object (streamed; see below) |
| `PUT` | `/{bucket}/{object}` with `x-amz-copy-source: /{src-bucket}/{src-key}` | Copy an object |
| `GET` | `/{bucket}/{object}` | Download an object (streamed) |
| `HEAD` | `/{bucket}/{object}` | Object metadata, no body |
| `DELETE` | `/{bucket}/{object}` | Delete an object (idempotent) |

`{object}` may itself contain `/` — folders are purely virtual, derived
from `/`-splitting `object_key` at query time; nothing is persisted for an
empty "folder".

**Upload semantics**: the request body is streamed directly to disk in
1&nbsp;MB chunks — never fully buffered in memory — while the MD5
checksum is computed incrementally. That checksum becomes both the
`ETag` response header and the object's stored `checksum`. Uploads are
capped by **Settings → Storage → Maximum Upload Size** (and by
`upload_max_filesize`/`post_max_size` in `php.ini`, which must be raised
to match). **Multipart upload is out of scope** — a single streamed `PUT`
only.

**Metadata**: any request header prefixed `x-amz-meta-` is captured as a
JSON key/value pair and echoed back on `GET`/`HEAD` — no server-side
content inspection.

**Move**: not a distinct endpoint. Client-driven Copy + Delete (`PUT` with
`x-amz-copy-source`, then `DELETE` the source), or use the Bucket Admin
panel's Object Browser "Move" action, which does the same thing
server-side in one request.

---

## Example cURL requests

Every example needs a `Date` header and a matching `Authorization`
signature computed as above. This snippet computes both:

```bash
ACCESS_KEY="AKxxxxxxxxxxxxxxxxxx"
SECRET_KEY="your-secret-key"
METHOD="PUT"
PATH_="/my-bucket"

sign() {
  php -r '
    $date = gmdate("D, d M Y H:i:s \G\M\T");
    $canonical = $argv[1]."\n".$argv[2]."\n".$date."\n"."";
    $sig = base64_encode(hash_hmac("sha256", $canonical, $argv[3], true));
    echo "Date: $date\n";
    echo "Authorization: HMAC ".$argv[4].":".$sig."\n";
  ' "$1" "$2" "$SECRET_KEY" "$ACCESS_KEY"
}
```

### Create a bucket

```bash
eval "$(sign PUT /my-bucket | sed 's/^/HDR_/' )"
curl -X PUT "https://your-host/my-bucket" \
  -H "Date: $(php -r 'echo gmdate("D, d M Y H:i:s \G\M\T");')" \
  -H "Authorization: HMAC AKxxxxxxxxxxxxxxxxxx:<signature>"
```

Simplest working form (compute both headers in one shot):

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "PUT\n/my-bucket\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl -X PUT "https://your-host/my-bucket" -H "Date: $DATE" -H "Authorization: $AUTH"
```

### Upload an object

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "PUT\n/my-bucket/docs/report.pdf\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl -X PUT "https://your-host/my-bucket/docs/report.pdf" \
  -H "Date: $DATE" -H "Authorization: $AUTH" \
  -H "Content-Type: application/pdf" \
  -H "x-amz-meta-author: jane" \
  --data-binary @report.pdf
```

### Download an object

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "GET\n/my-bucket/docs/report.pdf\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl -O -J "https://your-host/my-bucket/docs/report.pdf" -H "Date: $DATE" -H "Authorization: $AUTH"
```

### List objects

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "GET\n/my-bucket\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl "https://your-host/my-bucket?list-type=2&prefix=docs/" -H "Date: $DATE" -H "Authorization: $AUTH"
```

### Delete an object

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "DELETE\n/my-bucket/docs/report.pdf\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl -X DELETE "https://your-host/my-bucket/docs/report.pdf" -H "Date: $DATE" -H "Authorization: $AUTH"
```

### Copy an object

```bash
read DATE AUTH < <(php -r '
  $date = gmdate("D, d M Y H:i:s \G\M\T");
  $sig = base64_encode(hash_hmac("sha256", "PUT\n/other-bucket/copy-of-report.pdf\n".$date."\n", "your-secret-key", true));
  echo $date." HMAC AKxxxxxxxxxxxxxxxxxx:".$sig;
')
curl -X PUT "https://your-host/other-bucket/copy-of-report.pdf" \
  -H "Date: $DATE" -H "Authorization: $AUTH" \
  -H "x-amz-copy-source: /my-bucket/docs/report.pdf"
```

### Generate a pre-signed download URL

```bash
php -r '
  $secret = "your-secret-key";
  $expires = time() + 300; // 5 minutes
  $sig = rawurlencode(base64_encode(hash_hmac("sha256", "GET\n/my-bucket/docs/report.pdf\n".$expires, $secret, true)));
  echo "https://your-host/my-bucket/docs/report.pdf?AccessKey=AKxxxxxxxxxxxxxxxxxx&Expires=$expires&Signature=$sig".PHP_EOL;
'
```

No `Authorization` header needed — just open the URL in a browser or
`curl` it directly.

---

## Postman collection

A ready-to-import collection with a pre-request script that computes the
HMAC signature automatically (via CryptoJS, bundled with Postman) lives at
[`../postman/Storage-API.postman_collection.json`](../postman/Storage-API.postman_collection.json).
Set the collection variables `base_url`, `access_key` and `secret_key`
before running requests.

---

## Error responses

Non-2xx responses use the app's standard envelope:

```json
{ "status": 0, "message": "The specified bucket does not exist.", "data": [] }
```

| Status | Meaning |
|---|---|
| 401 | Missing/invalid/expired signature, inactive key, or anonymous access to a non-Public bucket |
| 403 | Valid key, but it doesn't own this bucket |
| 404 | Bucket or object not found |
| 409 | Bucket not empty (delete blocked) |
| 413 | Upload exceeds the configured maximum size |
| 415 | File extension not in the allowed-types list |
| 429 | Rate limit exceeded |

---

## Out of scope (v1)

Multipart uploads, IAM policies, object versioning, object tags,
lifecycle rules, replication, encryption at rest, and full AWS SDK
compatibility are explicitly not implemented — see "Out of Scope" and
"Future / Phase 2" in [`local/prd.md`](local/prd.md).
