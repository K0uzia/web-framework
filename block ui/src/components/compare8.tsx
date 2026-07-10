import {
  Accessibility,
  BadgeCheck,
  Figma,
  Gem,
  LayoutDashboard,
  ListChecks,
  Moon,
  Settings2,
  Type,
} from "lucide-react";
import { Check, X } from "lucide-react";
import React from "react";

import { cn } from "@/lib/utils";

type Feature = {
  icon: React.ReactNode;
  label: string;
  description: string;
  shadcn: true | false | "partial";
  bootstrap: true | false | "partial";
  tooltip?: { content: React.ReactNode };
};

const features: Feature[] = [
  {
    icon: <LayoutDashboard className="text-gray-500" />,
    label: "Design System",
    description: "Modern, utility-first vs classic, component-based.",
    shadcn: true,
    bootstrap: true,
  },
  {
    icon: <Settings2 className="text-gray-500" />,
    label: "Customization",
    description: "Highly customizable vs limited by default.",
    shadcn: true,
    bootstrap: false,
  },
  {
    icon: <Moon className="text-gray-500" />,
    label: "Dark Mode",
    description: "Built-in dark mode vs requires extra setup.",
    shadcn: true,
    bootstrap: false,
  },
  {
    icon: <Type className="text-gray-500" />,
    label: "TypeScript Support",
    description: "First-class TypeScript support vs partial support.",
    shadcn: true,
    bootstrap: "partial",
  },
  {
    icon: <Accessibility className="text-gray-500" />,
    label: "Accessibility",
    description: "Focus on accessibility (a11y) vs basic support.",
    shadcn: true,
    bootstrap: false,
  },
  {
    icon: <ListChecks className="text-gray-500" />,
    label: "Component Count",
    description: "30+ components vs 25+ components.",
    shadcn: true,
    bootstrap: true,
  },
  {
    icon: <BadgeCheck className="text-gray-500" />,
    label: "License",
    description: "MIT license for both.",
    shadcn: true,
    bootstrap: true,
  },
  {
    icon: <Gem className="text-gray-500" />,
    label: "Premium Components",
    description:
      "Premium components available in Shadcn, not included in Bootstrap.",
    shadcn: true,
    bootstrap: false,
    tooltip: {
      content: (
        <>
          <span className="mb-1 block font-semibold">Premium Only</span>
          Some advanced components are only available in paid versions or
          require third-party libraries.
        </>
      ),
    },
  },
  {
    icon: <Figma className="text-gray-500" />,
    label: "Figma Kit",
    description: "Official Figma kit available for Shadcn, not for Bootstrap.",
    shadcn: true,
    bootstrap: false,
    tooltip: {
      content: (
        <>
          <span className="mb-1 block font-semibold">
            Figma Kit Unavailable
          </span>
          Bootstrap does not provide an official Figma kit, but community kits
          may exist.
        </>
      ),
    },
  },
];

interface Compare8Props {
  className?: string;
}

const Compare8 = ({ className }: Compare8Props) => {
  return (
    <section className={cn("py-32", className)}>
      <div className="container">
        <h2 className="mb-4 text-center text-4xl font-semibold">Compare Us</h2>
        <p className="mb-8 text-center text-muted-foreground">
          A modern framework for building websites that is better than the
          competition.
        </p>
        <div className="mx-auto max-w-4xl divide-y divide-border overflow-x-auto rounded-lg border-border bg-background shadow">
          <div className="hidden rounded-t-lg bg-muted text-left text-base font-semibold text-foreground sm:flex">
            <div className="w-16 px-6 py-4"></div>
            <div className="flex-1 px-6 py-4">Feature</div>
            <div className="w-40 px-6 py-4">Shadcn</div>
            <div className="w-40 px-6 py-4">Bootstrap</div>
          </div>
          {features.map((row) => (
            <div
              key={row.label}
              className="flex flex-col items-start text-left sm:flex-row sm:items-center"
            >
              <div className="flex w-full items-center justify-start px-6 pt-4 sm:w-16 sm:justify-center sm:py-4">
                {row.icon}
                <span className="ml-3 text-base font-medium sm:hidden">
                  {row.label}
                </span>
              </div>
              <div className="w-full flex-1 px-6 pb-2 sm:py-4">
                <div className="hidden font-medium sm:block">{row.label}</div>
                <div className="mt-2 mb-2 text-sm text-muted-foreground sm:mb-0">
                  {row.description}
                </div>
              </div>
              <div className="flex w-full items-center justify-start px-6 pb-2 sm:w-40 sm:justify-center sm:py-4">
                {row.shadcn === true ? (
                  <Check className="size-5 text-green-600" />
                ) : row.shadcn === "partial" ? (
                  <Check className="size-5 text-yellow-500" />
                ) : (
                  <X className="size-5 text-destructive" />
                )}
                <span className="ml-2 text-xs font-medium text-muted-foreground sm:hidden">
                  Shadcn
                </span>
              </div>
              <div className="flex w-full items-center justify-start border-border px-6 pb-4 sm:w-40 sm:justify-center sm:border-0 sm:py-4">
                {row.bootstrap === true ? (
                  <Check className="size-5 text-green-600" />
                ) : row.bootstrap === "partial" ? (
                  <Check className="size-5 text-yellow-500" />
                ) : row.bootstrap === false && row.tooltip ? (
                  <span className="inline-block h-5">—</span>
                ) : (
                  <X className="size-5 text-destructive" />
                )}
                <span className="ml-2 text-xs font-medium text-muted-foreground sm:hidden">
                  Bootstrap
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export { Compare8 };
