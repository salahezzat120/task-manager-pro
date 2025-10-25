# Contributing to Task Manager Pro

Thank you for your interest in contributing to Task Manager Pro! We welcome contributions from the community and are pleased to have you join us.

## 📋 Table of Contents
- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Submitting Changes](#submitting-changes)
- [Coding Standards](#coding-standards)
- [Testing](#testing)

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🚀 Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/yourusername/task-manager-pro.git
   cd task-manager-pro
   ```
3. **Create a branch** for your changes:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 💻 Development Setup

### Prerequisites
- Node.js 16+ 
- PHP 8+
- MySQL 8+
- Git

### Installation
```bash
# Install frontend dependencies
npm install

# Set up backend
cd backend
# Import database schema
mysql -u root -p task_management < database-setup.sql

# Start development servers
npm run dev              # Frontend (port 5173)
php -S localhost:8000 -t public  # Backend (port 8000)
```

## 🔧 Making Changes

### Branch Naming
- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring

### Commit Messages
Follow conventional commits format:
```
type(scope): description

feat(auth): add password confirmation
fix(api): resolve task assignment validation
docs(readme): update installation instructions
```

## 📤 Submitting Changes

1. **Ensure your code follows our standards**
2. **Test your changes thoroughly**
3. **Update documentation if needed**
4. **Commit your changes**:
   ```bash
   git add .
   git commit -m "feat(tasks): add task filtering functionality"
   ```
5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```
6. **Create a Pull Request** on GitHub

### Pull Request Guidelines
- Provide a clear description of the changes
- Reference any related issues
- Include screenshots for UI changes
- Ensure all tests pass
- Request review from maintainers

## 📏 Coding Standards

### Frontend (React/TypeScript)
- Use TypeScript for all new code
- Follow React best practices and hooks patterns
- Use Tailwind CSS for styling
- Maintain component modularity
- Add proper error handling

### Backend (PHP)
- Follow PSR-12 coding standards
- Use PDO for database operations
- Implement proper input validation
- Add comprehensive error handling
- Document API endpoints

### General
- Write clear, self-documenting code
- Add comments for complex logic
- Use meaningful variable and function names
- Keep functions small and focused

## 🧪 Testing

### Frontend Testing
```bash
npm test                 # Run tests
npm run test:coverage    # Run with coverage
```

### Backend Testing
```bash
cd backend
php test-api.ps1         # Test API endpoints
```

### Manual Testing
- Test all user flows
- Verify responsive design
- Check error handling
- Validate security measures

## 🐛 Reporting Issues

When reporting issues, please include:
- Clear description of the problem
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- Environment details (OS, browser, versions)

## 💡 Feature Requests

We welcome feature requests! Please:
- Check if the feature already exists
- Provide clear use cases
- Explain the expected behavior
- Consider implementation complexity

## 📚 Documentation

Help improve our documentation:
- Fix typos and unclear explanations
- Add examples and use cases
- Update outdated information
- Translate to other languages

## 🏆 Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes
- Project documentation

## 📞 Getting Help

- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and general discussion
- **Email**: For private inquiries

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Task Manager Pro! 🚀
