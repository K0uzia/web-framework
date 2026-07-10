import React from "react";

import { cn } from "@/lib/utils";

interface Awards2Props {
  className?: string;
}

const Awards2 = ({ className }: Awards2Props) => {
  const awards = [
    { title: "Creative Collaboration Award", year: "2023" },
    { title: "Design Excellence Prize", year: "2023" },
    { title: "Innovation in Web Development", year: "2022" },
    { title: "Best User Experience Design", year: "2022" },
    { title: "Outstanding Visual Design", year: "2021" },
    { title: "Digital Innovation Award", year: "2021" },
    { title: "Creative Technology Excellence", year: "2020" },
    { title: "Best Interactive Design", year: "2020" },
    { title: "Web Design Achievement", year: "2019" },
  ];

  return (
    <section className={cn("py-32", className)}>
      <div className="container">
        <div className="grid grid-cols-1 gap-20 lg:grid-cols-6">
          <div className="top-10 col-span-2 flex h-fit w-fit items-center gap-3 py-8 lg:sticky">
            <span className="size-3 bg-orange-500" />
            <p className="text-2xl text-foreground/30 uppercase">AWARDS</p>
          </div>
          <ul className="col-span-4 w-full">
            {awards.map((award, index) => (
              <li key={index} className="flex justify-between border-b py-4">
                <h3 className="text-2xl font-medium tracking-tight">
                  {award.title}
                </h3>
                <p className="font-mono text-lg tracking-tighter text-foreground/50">
                  {award.year}
                </p>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </section>
  );
};

export { Awards2 };
