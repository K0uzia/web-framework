/**
 * Modèle canonique Builder ↔ Dashboard client.
 *
 * Runtime CapsulePHP : les permissions sont persistées dans
 * `site_settings.client_dashboard` sous la forme
 * `{ pages: { [slug]: { sections: { [sectionId]: { fields: string[] } } } } }`.
 *
 * Ces types décrivent le contrat mental Framer-like ; le mapping PHP
 * regroupe les champs par kind (text | image | link).
 */

export type FieldKind = "text" | "image" | "link" | "other";

/** Droits client sur un nœud (bloc ou champ). */
export interface ClientPermissions {
  /** Champs texte / textarea éditables en inline. */
  editableText: boolean;
  /** Images remplaçables (upload / médiathèque). */
  editableImage: boolean;
  /** Liens / href / boutons modifiables. */
  editableLink: boolean;
}

export const LOCKED_CLIENT_PERMISSIONS: ClientPermissions = {
  editableText: false,
  editableImage: false,
  editableLink: false,
};

export function isClientLocked(perms: ClientPermissions): boolean {
  return !perms.editableText && !perms.editableImage && !perms.editableLink;
}

/** Champ de contenu d'un bloc. */
export interface ContentFieldNode {
  key: string;
  kind: FieldKind;
  label: string;
  value?: unknown;
}

/**
 * Nœud sélectionnable sur le canvas (section / bloc).
 * Aligné sur le JSON sections SQLite de CapsulePHP.
 */
export interface ElementNode {
  id: string;
  type: string;
  variant: string;
  visible: boolean;
  label?: string;
  content: Record<string, unknown>;
  style: Record<string, unknown>;
  fields: ContentFieldNode[];
  clientPermissions: ClientPermissions;
}

/** Page du site (publique) ou page d'espace client. */
export type PageSpace = "public" | "client";

export interface PageNode {
  slug: string;
  title: string;
  path: string;
  space: PageSpace;
  published: boolean;
  sections: ElementNode[];
}

/**
 * Construit les permissions agrégées à partir d'une allowlist de clés
 * (modèle PHP actuel) et du schéma de champs du bloc.
 */
export function permissionsFromAllowedFields(
  allowed: string[],
  fields: ContentFieldNode[],
): ClientPermissions {
  const set = new Set(allowed);
  const has = (kind: FieldKind) =>
    fields.some((f) => f.kind === kind && set.has(f.key));

  return {
    editableText: has("text"),
    editableImage: has("image"),
    editableLink: has("link"),
  };
}

/**
 * Inverse : à partir des toggles kind, produit la liste de clés à autoriser.
 */
export function allowedFieldsFromPermissions(
  perms: ClientPermissions,
  fields: ContentFieldNode[],
): string[] {
  return fields
    .filter((f) => {
      if (f.kind === "text") return perms.editableText;
      if (f.kind === "image") return perms.editableImage;
      if (f.kind === "link") return perms.editableLink;
      return false;
    })
    .map((f) => f.key);
}
