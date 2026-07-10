import { ArrowRight } from "lucide-react";

import { cn } from "@/lib/utils";

interface Role {
  title: string;
  location: string;
  href: string;
}

interface Department {
  title: string;
  roles: Role[];
  className?: string;
}

interface Careers1Props {
  title?: string;
  description?: string;
  departments?: Department[];
  className?: string;
}

const Careers1 = ({
  title = "Careers",
  description = `We publish roles as soon as hiring managers open a requisition. Teams are grouped so candidates can scan by function without loading a separate filter.

Each row links to the full description on your ATS or careers site. Remote and office locations appear on the second line in smaller type.

If nothing matches today, we still welcome general applications through the contact form.`,
  departments = [
    {
      title: "Sales",
      roles: [
        {
          title: "Sales Manager",
          location: "London",
          href: "#",
        },
        {
          title: "Sales Development Representative",
          location: "London",
          href: "#",
        },
        {
          title: "Sales Manager",
          location: "London",
          href: "#",
        },
      ],
    },
    {
      title: "Customer Success",
      roles: [
        {
          title: "Customer Success Associate",
          location: "London",
          href: "#",
        },
      ],
    },
  ],
  className,
}: Careers1Props) => {
  return (
    <section className={cn("bg-background py-32", className)}>
      <div className="container">
        <h2 className="text-4xl font-medium md:text-6xl">{title}</h2>
        <p className="mt-6 whitespace-pre-wrap text-muted-foreground md:mb-20 md:text-lg">
          {description}
        </p>
        {departments.map((department) => (
          <div key={department.title} className="mt-12 md:mt-20">
            <h3 className="mb-8 text-3xl font-medium md:text-4xl">
              {department.title}
            </h3>
            <ul className="divide-y divide-border border-y border-border">
              {department.roles.map((role, index) => (
                <li key={index} className="group">
                  <a href={role.href} className="flex items-center py-6">
                    <div>
                      <div className="font-medium md:text-lg">{role.title}</div>
                      <div className="text-xs text-muted-foreground md:mt-2 md:text-sm">
                        {role.location}
                      </div>
                    </div>
                    <ArrowRight className="ml-auto size-6 -translate-x-6 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100" />
                  </a>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>
    </section>
  );
};

export { Careers1 };
