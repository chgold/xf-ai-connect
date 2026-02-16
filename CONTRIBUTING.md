# Contributing to XenForo AI Connect

Thank you for your interest in contributing! 🎉

## Ways to Contribute

- 🐛 **Report Bugs** - Open an issue with details
- 💡 **Suggest Features** - Share your ideas
- 📝 **Improve Documentation** - Help make docs clearer
- 🔧 **Submit Code** - Fix bugs or add features

## Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/xenforo-ai-connect.git
   cd xenforo-ai-connect
   ```

2. **Install XenForo**
   - Download XenForo 2.2.8+
   - Set up local development environment
   - Install the addon from `upload/` directory

3. **Make Changes**
   - Create a new branch for your feature
   - Make your changes
   - Test thoroughly

## Coding Standards

- Follow XenForo coding standards
- Use PSR-12 style for PHP code
- Add comments for complex logic
- Update documentation as needed

## Testing

Before submitting:
- ✅ Test manifest endpoint
- ✅ Test authentication
- ✅ Test all tools
- ✅ Check for PHP errors
- ✅ Verify routes work correctly

## Pull Request Process

1. **Fork** the repository
2. **Create** a feature branch
3. **Commit** your changes with clear messages
4. **Push** to your fork
5. **Submit** a pull request

### Commit Message Format

```
<type>: <short summary>

<detailed description>

Fixes #<issue-number>
```

**Types**: `fix`, `feat`, `docs`, `style`, `refactor`, `test`, `chore`

### Example

```
feat: Add OAuth2 authentication support

- Implemented OAuth2 provider integration
- Added new auth endpoint for token exchange
- Updated documentation

Fixes #42
```

## Code Review

All submissions require review. We'll:
- Check code quality
- Verify functionality
- Suggest improvements
- Test in various environments

## Questions?

Feel free to open an issue for discussion before starting work on major features.

---

**Thank you for contributing!** 🙌
