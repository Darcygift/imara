Security Policy for Smart Rent

## Reporting Security Vulnerabilities

If you discover a security vulnerability, please email security@smartrent.app instead of using the issue tracker.

Include the following information:
- Type of vulnerability
- Location in the codebase
- Potential impact
- Suggested fix (if any)

## Security Best Practices

### Authentication & Authorization
- All passwords are hashed using bcryptjs with 10 salt rounds
- JWT tokens expire after 7 days
- Tokens are validated on every protected route
- Passwords must meet minimum strength requirements:
  - At least 8 characters
  - Include uppercase and lowercase letters
  - Include numbers
  - Include special characters

### Data Protection
- All sensitive data (passwords, tokens, API keys) are never logged
- Environment variables are used for all secrets
- Database queries use parameterized statements to prevent SQL injection
- User data is validated before storage

### API Security
- CORS is configured to allow only trusted origins
- Rate limiting is implemented to prevent abuse
- Request validation with schema checking
- All API responses sanitize sensitive data

### Infrastructure Security
- Docker containers run as non-root user
- Environment variables are injected at runtime
- HTTPS/TLS is enforced in production
- Database connections use secure credentials

### Deployment Security
- Secrets are managed through environment variables
- No credentials are committed to version control
- CI/CD pipeline includes security scanning
- Docker images are scanned for vulnerabilities

## Security Checklist

Before deploying to production, ensure:

- [ ] All environment variables are set correctly
- [ ] JWT_SECRET is changed from default
- [ ] Database password is strong
- [ ] HTTPS is configured
- [ ] CORS whitelist is properly set
- [ ] Rate limiting is enabled
- [ ] Input validation is active
- [ ] Error messages don't expose internal details
- [ ] Logging doesn't capture sensitive data
- [ ] Dependency vulnerabilities are resolved
- [ ] OWASP Top 10 issues are addressed
- [ ] Security headers are configured (CSP, X-Frame-Options, etc.)

## Dependency Management

Run regular security audits:
```bash
npm audit
npm audit fix
```

Keep dependencies updated:
```bash
npm update
npm outdated
```

## OAuth & Social Login (When Implemented)

- Use official SDKs from providers
- Never store user passwords from external providers
- Validate state parameter to prevent CSRF
- Use HTTPS-only redirect URIs
- Implement proper scoping

## Rate Limiting

Current implementation uses:
- 100 requests per 15 minutes per IP for public endpoints
- 1000 requests per 15 minutes per user for authenticated endpoints

## Monitoring & Logging

- All failed authentication attempts are logged
- Suspicious activities trigger alerts
- Logs are retained for 30 days
- Personally identifiable information is not logged

## Compliance

Smart Rent is designed to comply with:
- GDPR (General Data Protection Regulation)
- CCPA (California Consumer Privacy Act)
- PCI DSS (if handling payment cards)
- Local data protection laws

## Security Updates

- Critical vulnerabilities: Patch within 24 hours
- High vulnerabilities: Patch within 7 days
- Medium vulnerabilities: Patch within 30 days
- Low vulnerabilities: Patch in next release

## Incident Response

In case of a security incident:
1. Assess the severity
2. Isolate affected systems
3. Notify affected users
4. Document the incident
5. Implement fixes
6. Review and prevent recurrence

## Contact

For security inquiries: security@smartrent.app
