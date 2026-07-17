"use client";

import * as React from "react";
import { cn } from "@/lib/utils";
import type { ClientPermissions, FieldKind } from "./types";
import { isClientLocked } from "./types";

export type InlineEditableMode = "text" | "image" | "link";

export interface InlineEditableProps {
  /** Identifiant stable (sectionId + field key) pour la persistance. */
  fieldId: string;
  mode: InlineEditableMode;
  permissions: ClientPermissions;
  value: string;
  /** Alt / aria pour image. */
  alt?: string;
  className?: string;
  children?: React.ReactNode;
  onChange?: (next: string) => void;
  onOpenMediaPicker?: () => void;
}

function modeAllowed(mode: InlineEditableMode, perms: ClientPermissions): boolean {
  if (mode === "text") return perms.editableText;
  if (mode === "image") return perms.editableImage;
  return perms.editableLink;
}

/**
 * Enveloppe générique pour l'édition inline côté client.
 * - Lecture seule si le kind n'est pas autorisé.
 * - Survol subtil (primary à 10 %) uniquement si éditable.
 * - Texte : double-clic → contenteditable + toolbar flottante minimale.
 * - Image : clic → callback médiathèque.
 * - Lien : clic → popover URL.
 */
export function InlineEditable({
  fieldId,
  mode,
  permissions,
  value,
  alt = "",
  className,
  children,
  onChange,
  onOpenMediaPicker,
}: InlineEditableProps) {
  const editable = modeAllowed(mode, permissions);
  const locked = isClientLocked(permissions) || !editable;

  const [editing, setEditing] = React.useState(false);
  const [draft, setDraft] = React.useState(value);
  const [linkOpen, setLinkOpen] = React.useState(false);
  const ref = React.useRef<HTMLElement | null>(null);

  React.useEffect(() => {
    setDraft(value);
  }, [value]);

  const commitText = React.useCallback(() => {
    const next = (ref.current?.innerText ?? draft).trim();
    setEditing(false);
    if (next !== value) {
      onChange?.(next);
    }
  }, [draft, onChange, value]);

  if (locked && mode === "text") {
    return (
      <span className={className} data-field-id={fieldId} data-client-locked="true">
        {children ?? value}
      </span>
    );
  }

  if (mode === "image") {
    return (
      <button
        type="button"
        data-field-id={fieldId}
        disabled={!editable}
        className={cn(
          "relative block max-w-full border-0 bg-transparent p-0 text-left",
          editable &&
            "cursor-pointer hover:outline hover:outline-2 hover:outline-dashed hover:outline-[color-mix(in_srgb,var(--color-primary,#3b82f6)_55%,transparent)] hover:bg-[color-mix(in_srgb,var(--color-primary,#3b82f6)_10%,transparent)]",
          className,
        )}
        onClick={() => {
          if (editable) onOpenMediaPicker?.();
        }}
        aria-label={editable ? "Remplacer l'image" : alt || "Image"}
      >
        {children ?? (
          <img src={value} alt={alt} className="max-w-full h-auto block" />
        )}
      </button>
    );
  }

  if (mode === "link") {
    return (
      <span className={cn("relative inline-flex", className)} data-field-id={fieldId}>
        <button
          type="button"
          disabled={!editable}
          className={cn(
            "border-0 bg-transparent p-0 font-inherit text-inherit",
            editable &&
              "cursor-pointer rounded-sm hover:bg-[color-mix(in_srgb,var(--color-primary,#3b82f6)_10%,transparent)]",
          )}
          onClick={() => {
            if (editable) setLinkOpen((o) => !o);
          }}
        >
          {children ?? value}
        </button>
        {linkOpen && editable ? (
          <span
            className="absolute left-0 top-full z-50 mt-1 flex min-w-[16rem] gap-2 rounded-md border border-[var(--color-border,#e2e8f0)] bg-[var(--color-background,#fff)] p-2 shadow-md"
            role="dialog"
            aria-label="Modifier le lien"
          >
            <input
              type="url"
              className="min-w-0 flex-1 rounded border border-[var(--color-border,#e2e8f0)] px-2 py-1 text-sm"
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
              placeholder="https://"
              aria-label="URL du lien"
            />
            <button
              type="button"
              className="rounded px-2 py-1 text-sm font-semibold text-[var(--color-button-primary-text,#fff)] bg-[var(--color-primary,#3b82f6)]"
              onClick={() => {
                onChange?.(draft);
                setLinkOpen(false);
              }}
            >
              OK
            </button>
          </span>
        ) : null}
      </span>
    );
  }

  // mode === "text"
  return (
    <span className={cn("relative inline-block max-w-full", className)}>
      <span
        ref={(node) => {
          ref.current = node;
        }}
        data-field-id={fieldId}
        contentEditable={editing}
        suppressContentEditableWarning
        className={cn(
          "rounded-sm outline-none",
          editable &&
            "cursor-text hover:bg-[color-mix(in_srgb,var(--color-primary,#3b82f6)_10%,transparent)]",
          editing && "ring-2 ring-[var(--color-primary,#3b82f6)] bg-[var(--color-background,#fff)]",
        )}
        onDoubleClick={() => {
          if (!editable) return;
          setEditing(true);
          requestAnimationFrame(() => ref.current?.focus());
        }}
        onBlur={() => {
          if (editing) commitText();
        }}
        onKeyDown={(e) => {
          if (e.key === "Escape") {
            setEditing(false);
            if (ref.current) ref.current.innerText = value;
          }
          if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            commitText();
          }
        }}
      >
        {children ?? value}
      </span>
      {editing ? (
        <span
          className="absolute -top-10 left-0 z-50 flex gap-1 rounded-md border border-[var(--color-border,#e2e8f0)] bg-[var(--color-background,#fff)] p-1 shadow-md"
          role="toolbar"
          aria-label="Mise en forme"
        >
          <ToolbarBtn
            label="Gras"
            onMouseDown={(e) => {
              e.preventDefault();
              document.execCommand("bold");
            }}
          >
            <strong>B</strong>
          </ToolbarBtn>
          <ToolbarBtn
            label="Italique"
            onMouseDown={(e) => {
              e.preventDefault();
              document.execCommand("italic");
            }}
          >
            <em>I</em>
          </ToolbarBtn>
          <ToolbarBtn
            label="Liste"
            onMouseDown={(e) => {
              e.preventDefault();
              document.execCommand("insertUnorderedList");
            }}
          >
            •
          </ToolbarBtn>
        </span>
      ) : null}
    </span>
  );
}

function ToolbarBtn({
  label,
  children,
  onMouseDown,
}: {
  label: string;
  children: React.ReactNode;
  onMouseDown: (e: React.MouseEvent) => void;
}) {
  return (
    <button
      type="button"
      aria-label={label}
      className="inline-flex h-7 w-7 items-center justify-center rounded text-sm hover:bg-[var(--color-surface,#f8fafc)]"
      onMouseDown={onMouseDown}
    >
      {children}
    </button>
  );
}

export function kindToMode(kind: FieldKind): InlineEditableMode | null {
  if (kind === "text") return "text";
  if (kind === "image") return "image";
  if (kind === "link") return "link";
  return null;
}
