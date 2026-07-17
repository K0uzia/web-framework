"use client";

import { cn } from "@/lib/utils";

export interface PublishBarProps {
  dirty: boolean;
  saving?: boolean;
  statusLabel?: string;
  onPublish: () => void;
  className?: string;
}

/**
 * Barre fixe bas d'écran (style Notion) pour le dashboard client inline.
 * Couleur primaire via tokens CSS du thème site.
 */
export function PublishBar({
  dirty,
  saving = false,
  statusLabel,
  onPublish,
  className,
}: PublishBarProps) {
  const status =
    statusLabel ??
    (saving
      ? "Enregistrement…"
      : dirty
        ? "Modifications non enregistrées"
        : "Toutes les modifications sont enregistrées");

  return (
    <div
      className={cn(
        "fixed inset-x-0 bottom-0 z-50 border-t border-[var(--color-border,#e2e8f0)]",
        "bg-[color-mix(in_srgb,var(--color-background,#fff)_92%,transparent)] backdrop-blur-sm",
        "px-4 py-3",
        className,
      )}
      role="region"
      aria-label="Sauvegarde du contenu"
    >
      <div className="mx-auto flex max-w-5xl items-center justify-between gap-3">
        <p
          className={cn(
            "m-0 text-sm",
            dirty
              ? "text-[var(--color-warning,#d97706)]"
              : "text-[var(--color-success,#16a34a)]",
          )}
          aria-live="polite"
        >
          {status}
        </p>
        <button
          type="button"
          disabled={!dirty || saving}
          onClick={onPublish}
          className={cn(
            "inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold",
            "bg-[var(--color-primary,#3b82f6)] text-[var(--color-button-primary-text,#fff)]",
            "disabled:cursor-not-allowed disabled:opacity-45",
          )}
        >
          Publier les modifications
        </button>
      </div>
    </div>
  );
}
