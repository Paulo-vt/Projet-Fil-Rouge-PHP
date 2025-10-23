<?php

abstract class Question {
    protected int $id;
    protected string $texteQuestion;
    protected array $reponses;
    protected int $bonneReponse;
    protected ?string $explication;

    public function __construct(
        int $id,
        string $texteQuestion,
        array $reponses,
        int $bonneReponse,
        ?string $explication = null
    ) {

        $this->id = $id;
        $this->texteQuestion = $texteQuestion;
        $this->reponses = $reponses;
        $this->bonneReponse = $bonneReponse;
        $this->explication = $explication;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTexteQuestion(): string {
        return $this->texteQuestion;
    }

    public function getReponses(): array {
        return $this->reponses;
    }

    public function getBonneReponse(): int {
        return $this->bonneReponse;
    }

    public function getExplication(): ?string {
        return $this->explication;
    }

    public function estCorrect(int $reponseUtilisateur): bool {
        return $reponseUtilisateur === $this->bonneReponse;
    }

    abstract public function afficherHTML(int $index): string;

    abstract public function getType(): string;
}
