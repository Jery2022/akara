const { execSync } = require('child_process');
const fs = require('fs');

function getGitCommits() {
  // Récupère tous les commits depuis la dernière version taguée (ou depuis le début)
  // Ici on récupère les commits depuis le tag v1.0.0, à adapter selon vos tags
  try {
    const commits = execSync(
      'git log v1.0.0..HEAD --pretty=format:"%s"'
    ).toString();
    return commits.split('\n');
  } catch (error) {
    // Si pas de tag v1.0.0, récupère tous les commits
    const commits = execSync('git log --pretty=format:"%s"').toString();
    return commits.split('\n');
  }
}

function parseCommits(commits) {
  const changelog = {
    feat: [],
    fix: [],
    docs: [],
    style: [],
    refactor: [],
    perf: [],
    test: [],
    chore: [],
    others: [],
  };

  commits.forEach((commit) => {
    const match = commit.match(/^(\w+)(\(.+\))?:\s(.+)$/);
    if (match) {
      const type = match[1];
      const message = match[3];
      if (changelog[type]) {
        changelog[type].push(message);
      } else {
        changelog.others.push(commit);
      }
    } else {
      changelog.others.push(commit);
    }
  });

  return changelog;
}

function generateMarkdown(
  changelog,
  version = 'Unreleased',
  date = new Date().toISOString().split('T')[0]
) {
  let md = `## [${version}] - ${date}\n\n`;

  const sections = {
    feat: '### Ajouts',
    fix: '### Corrections',
    docs: '### Documentation',
    style: '### Style',
    refactor: '### Refactorisation',
    perf: '### Performance',
    test: '### Tests',
    chore: '### Tâches diverses',
    others: '### Autres',
  };

  for (const [key, title] of Object.entries(sections)) {
    if (changelog[key].length > 0) {
      md += `${title}\n`;
      changelog[key].forEach((item) => {
        md += `- ${item}\n`;
      });
      md += '\n';
    }
  }

  return md;
}

function updateChangelogFile(content) {
  const changelogPath = './CHANGELOG.md';
  let existingContent = '';

  if (fs.existsSync(changelogPath)) {
    existingContent = fs.readFileSync(changelogPath, 'utf8');
  }

  const newContent =
    `# Journal des modifications (Changelog) - AKARA\n\n` +
    `Tous les changements notables sont listés ici.\n\n` +
    content +
    '\n' +
    existingContent;

  fs.writeFileSync(changelogPath, newContent, 'utf8');
  console.log('CHANGELOG.md mis à jour avec succès.');
}

// Exécution
const commits = getGitCommits();
const parsed = parseCommits(commits);
const markdown = generateMarkdown(
  parsed,
  'v1.4.0',
  new Date().toISOString().split('T')[0]
);
updateChangelogFile(markdown);
