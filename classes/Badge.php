<?php
class Badge {
    private int $id;
    private string $nom;
    private string $description;
    private ?string $dateObtention;

    public function __construct(
        int $id,
        string $nom,
        string $description,
        ?string $dateObtention = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->dateObtention = $dateObtention;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): string { return $this->description; }
    public function getDateObtention(): ?string { return $this->dateObtention; }

    public function isUnlocked(): bool {
        return $this->dateObtention !== null;
    }

    public function afficherHTML(bool $showDescription = true): string {
        $html = '';
        $isLocked = !$this->isUnlocked();
        $opacityClass = $isLocked ? 'opacity-50' : '';
        
        $html .= '<div class="bg-white rounded-xl shadow-lg p-6 text-center transform transition-all duration-300 hover:scale-105 ' . $opacityClass . '">';
        $html .= '<div class="text-6xl mb-3">🏆</div>';
        $html .= '<h3 class="text-xl font-bold text-gray-800 mb-2">' . htmlspecialchars($this->nom) . '</h3>';
        
        if ($showDescription) {
            $html .= '<p class="text-sm text-gray-600 mb-3">' . htmlspecialchars($this->description) . '</p>';
        }
        
        if ($isLocked) {
            $html .= '<div class="inline-block bg-gray-200 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">🔒 Verrouillé</div>';
        } else {
            $html .= '<div class="inline-block bg-gradient-to-r from-green-400 to-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">✓ Débloqué</div>';
            if ($this->dateObtention) {
                $date = new DateTime($this->dateObtention);
                $html .= '<p class="text-xs text-gray-500 mt-2">Le ' . $date->format('d/m/Y à H:i') . '</p>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }
}
