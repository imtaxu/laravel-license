---
name: Feature request
about: Suggest an idea for this project
title: ''
labels: 'enhancement'
assignees: ''

---

**Is your feature request related to a problem? Please describe.**
A clear and concise description of what the problem is. Ex. I'm always frustrated when [...]

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Feature Category**
Which category does this feature fall into? (You can select multiple)
- [ ] License Verification Mechanism
- [ ] Security Features
- [ ] User Interface / UX
- [ ] License Features Field
- [ ] API Integration
- [ ] Performance Improvements
- [ ] Documentation
- [ ] Other

**Is it related to the License Features Field?**
If your feature request is related to the `features` field, please provide the following information:

- Suggested JSON structure:
```json
{
  "example_feature": true,
  "example_limit": 100
}
```

- Example code for how this feature would be used:
```php
if ($license->hasFeature('example_feature')) {
    // Feature is enabled
}

$limit = $license->hasFeature('example_limit', 50); // Default: 50
```

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.
