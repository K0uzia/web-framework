"use client";

import { Switch } from "@/components/ui/switch";
import type { ClientPermissions } from "./types";
import { isClientLocked } from "./types";

export interface ClientAccessPanelProps {
  value: ClientPermissions;
  onChange: (next: ClientPermissions) => void;
  available?: Partial<Record<keyof ClientPermissions, boolean>>;
}

/**
 * Section Inspector "Accès Client" (référence UI React).
 * Le runtime /dev utilise l'équivalent PHP dans SectionFormRenderer.
 */
export function ClientAccessPanel({
  value,
  onChange,
  available = {
    editableText: true,
    editableImage: true,
    editableLink: true,
  },
}: ClientAccessPanelProps) {
  const locked = isClientLocked(value);

  const set = (key: keyof ClientPermissions, checked: boolean) => {
    onChange({ ...value, [key]: checked });
  };

  return (
    <section
      className="mt-4 border-t border-[var(--color-border,#e2e8f0)] pt-4"
      aria-labelledby="client-access-title"
    >
      <h3
        id="client-access-title"
        className="mb-1 text-sm font-semibold tracking-tight"
      >
        Accès Client
      </h3>
      <p className="mb-3 text-xs text-[var(--color-text-muted,#64748b)]">
        Choisissez ce que le client peut modifier sur ce bloc.
      </p>

      <div className="flex flex-col gap-2">
        {available.editableText !== false ? (
          <AccessRow
            id="ca-text"
            label="Rendre le texte modifiable"
            checked={value.editableText}
            onCheckedChange={(v) => set("editableText", v)}
          />
        ) : null}
        {available.editableImage !== false ? (
          <AccessRow
            id="ca-image"
            label="Rendre l'image remplaçable"
            checked={value.editableImage}
            onCheckedChange={(v) => set("editableImage", v)}
          />
        ) : null}
        {available.editableLink !== false ? (
          <AccessRow
            id="ca-link"
            label="Rendre le lien modifiable"
            checked={value.editableLink}
            onCheckedChange={(v) => set("editableLink", v)}
          />
        ) : null}
      </div>

      {locked ? (
        <p className="mt-3 text-xs text-[var(--color-text-muted,#64748b)]">
          Ce bloc est verrouillé en lecture seule pour le client.
        </p>
      ) : null}
    </section>
  );
}

function AccessRow({
  id,
  label,
  checked,
  onCheckedChange,
}: {
  id: string;
  label: string;
  checked: boolean;
  onCheckedChange: (v: boolean) => void;
}) {
  return (
    <label
      htmlFor={id}
      className="flex items-center justify-between gap-3 rounded-md border border-[var(--color-border,#e2e8f0)] bg-[var(--color-surface,#f8fafc)] px-3 py-2.5 text-sm"
    >
      <span>{label}</span>
      <Switch
        id={id}
        checked={checked}
        onCheckedChange={onCheckedChange}
        size="sm"
      />
    </label>
  );
}
