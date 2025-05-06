<?php

abstract class BaseViewModel {
    abstract public function viewName(): string;
    abstract public function fields(): array;
    abstract public function primaryKey(): string;
}