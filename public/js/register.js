// Gestion des compétences (tags)
const skills = [];
const skillInput = document.getElementById('skillInput');
const addSkillBtn = document.getElementById('addSkillBtn');
const skillsContainer = document.getElementById('skillsContainer');
const skillsInputs = document.getElementById('skillsInputs');

function updateSkillsDisplay() {
    // Afficher les badges
    skillsContainer.innerHTML = skills.map((skill, index) => `
        <span class="skill-tag">
            ${skill}
            <button type="button" onclick="removeSkill(${index})">
                <i class="fas fa-times"></i>
            </button>
        </span>
    `).join('');
    
    // Mettre à jour les champs hidden pour l'envoi au serveur
    skillsInputs.innerHTML = skills.map(skill => 
        `<input type="hidden" name="skills[]" value="${skill}">`
    ).join('');
}

function addSkill() {
    const skill = skillInput.value.trim();
    if (skill && !skills.includes(skill)) {
        skills.push(skill);
        updateSkillsDisplay();
        skillInput.value = '';
    }
}

function removeSkill(index) {
    skills.splice(index, 1);
    updateSkillsDisplay();
}

// Ajouter une skill avec le bouton
addSkillBtn.addEventListener('click', addSkill);

// Ajouter une skill avec la touche Entrée
skillInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addSkill();
    }
});

// Debug: afficher les données avant soumission
document.getElementById('studentForm').addEventListener('submit', (e) => {
    console.log('Form submitted with skills:', skills);
    // Le formulaire sera soumis normalement
});